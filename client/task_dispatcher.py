#!/usr/bin/env python3

import os
import sys
import copy
import json
import logging
import requests
import functools
import subprocess
import argparse
from dataclasses import dataclass
from typing_extensions import Self
from argparse import ArgumentParser

logger = logging.getLogger()
logger.setLevel(logging.DEBUG)

class ApiConfigException(Exception):
    pass

class ApiConfigEmptyEnvVariableException(Exception):
    def __init__(self, name: str):
        super().__init__(f'Env variable is empty: {name}')

class ApiException(Exception):
    pass

@dataclass
class ApiConfig:
    api_url: str
    api_key: str

    env_mapping = {
        'api_url': 'API_URL',
        'api_key': 'API_KEY',
    }

    @staticmethod
    def from_env() -> Self:
        kwargs = {}
        for key, env_variable in ApiConfig.env_mapping.items():
            value = os.getenv(env_variable)
            if value is None:
                raise ApiConfigEmptyEnvVariableException(env_variable)
            kwargs[key] = value

        return ApiConfig(**kwargs)

@dataclass
class Task:
    id: str
    input: str

@dataclass
class Result:
    id: str
    output: str

class Api:
    def __init__(self, api_config: ApiConfig):
        self.api_config = api_config
    
    def send_request(self, method: str, url: str, **kwargs: dict) -> requests.Response:
        kwargs = copy.deepcopy(kwargs)
        if not 'headers' in kwargs:
            kwargs['headers'] = {}
        kwargs['headers']['X-AUTH-TOKEN'] = self.api_config.api_key

        response = requests.request(method, self.api_config.api_url + url, **kwargs)
        if response.status_code == 401:
            raise ApiException(f'Invalid API key: {self.api_config.api_key}')
        return response

    def verify_status_code(self, response: requests.Response, expected_status_code: int, error_message: str) -> None:
        data = response.json()
        if response.status_code != expected_status_code:
            if 'detail' in data:
                error_message += ': ' + data['detail']
            raise ApiException(error_message)


    def make_reservation(self) -> Task:
        response = self.send_request('POST', '/reservations')
        self.verify_status_code(response, 201, 'Could not make a reservation')

        task = response.json()['task']
        return Task(
            id=task['id'],
            input=task['input'],
        )
    
    def finish_reservation(self, result: Result) -> None:
        response = self.send_request('DELETE', f'/reservations/{result.id}', json=result.output)
        self.verify_status_code(response, 200, 'Could not finish reservation')


def split_list_at(l: list, el: str) -> list:
    try:
        index = l.index(el)
        return (l[:index], l[index + 1:])
    except ValueError:
        return (l, [])

def collect_cli_args() -> argparse.Namespace:
    sys.argv, subprocess_args = split_list_at(sys.argv, '--')

    argument_parser = ArgumentParser()
    argument_parser.add_argument('--loop', '-l', help='Run in loop', default=False, action='store_true')
    return (
        argument_parser.parse_args(),
        subprocess_args
    )

def solve_task(task: Task, process: subprocess.Popen) -> Result:
    process.stdin.write(json.dumps(task.input) + '\n')
    process.stdin.flush()

    line = process.stdout.readline()
    output = json.loads(line.strip())

    return Result(
        id=task.id,
        output=output,
    )

def single_task_cycle(api: Api, process: subprocess.Popen):
    task = api.make_reservation()
    logger.info(f"Reserved task with id '{task.id}'")
    result = solve_task(task, process)
    api.finish_reservation(result)

def infinite_loop(fn):
    @functools.wraps(fn)
    def wrapper(*args, **kwargs):
        while True:
            fn(*args, **kwargs)

    return wrapper


def main() -> None:
    dispatcher_args, subprocess_args = collect_cli_args()

    api_config = ApiConfig.from_env()
    api = Api(api_config)
    process = subprocess.Popen(
        subprocess_args,
        stdin=subprocess.PIPE,
        stdout=subprocess.PIPE,
        text=True,
    )

    callable = lambda: single_task_cycle(api, process)
    if dispatcher_args.loop:
        callable = infinite_loop(callable)

    try:
        callable()
    except KeyboardInterrupt:
        pass
    except Exception as e:
        logger.error(str(e))
        sys.exit(1)

if __name__ == '__main__':
    main()
