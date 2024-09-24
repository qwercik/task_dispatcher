import json
import requests
from dataclasses import dataclass

class ApiException(Exception):
    pass

class NoTasksAvailableException(ApiException):
    pass

@dataclass
class TaskData:
    id: int
    params: dict

class TaskDispatcher:
    def __init__(self, api_url: str, api_key: str):
        self.api_url = api_url
        self.api_key = api_key

    def fetch_task(self) -> TaskData:
        response = self._send_request('POST', '/tasks/reservations')
        if response.status_code == 409:
            raise NoTasksAvailableException()
        self._verify_status_code(response, 201, 'Could not get tasks')
        return TaskData(**response.json()['task'])

    def add_body_result(self, task_data: TaskData, data, mime_type) -> None:
        response = self._send_request('POST', f'/tasks/{task_data.id}/results', data=data, headers={'Content-Type': mime_type})
        self._verify_status_code(response, 201, 'Could not send a result')

    def add_file_result(self, task_data: TaskData, stream) -> None:
        response = self._send_request('POST', f'/tasks/{task_data.id}/results', files={'file': stream})
        self._verify_status_code(response, 201, 'Could not send a result')

    def finish_task(self, task_data: TaskData, finished: bool):
        finished = '1' if finished else '0'
        response = self._send_request('DELETE', f'/tasks/reservations/{task_data.id}?finished={finished}')
        self._verify_status_code(response, 200, 'Could not finish reservation')

    def _send_request(self, method: str, url: str, **kwargs: dict) -> requests.Response:
        if not 'headers' in kwargs:
            kwargs['headers'] = {}
        kwargs['headers']['X-AUTH-TOKEN'] = self.api_key

        response = requests.request(method, self.api_url + url, **kwargs)
        if response.status_code == 401:
            raise ApiException(f'Invalid API key: {self.api_key}')
        return response
    
    def _verify_status_code(self, response: requests.Response, expected_status_code: int, error_message: str) -> None:
        if response.status_code != expected_status_code:
            data = response.json()
            if 'detail' in data:
                error_message += ': ' + data['detail']
            raise ApiException(error_message)

class Task:
    def __init__(self, dispatcher: TaskDispatcher):
        self.api = dispatcher
        self.data = self.api.fetch_task()
        self.finished = False

    @staticmethod
    def reserve(dispatcher: TaskDispatcher):
        return Task(dispatcher)

    def params(self):
        return self.data.params
    
    def add_result(self, content=None, mime_type=None, json_data=None, stream=None):
        if stream is not None:
            self.api.add_file_result(self.data, stream)
        if json_data is not None:
            self.api.add_body_result(self.data, json.dumps(json_data), 'application/json')
        if content is not None and mime_type is not None:
            self.api.add_body_result(self.data, content, mime_type)

    def finish(self):
        self.finished = True

    def __enter__(self):
        return self

    def __exit__(self, exc_type, exc_value, traceback):
        self.api.finish_task(self.data, self.finished)
