#!/usr/bin/env python3

import sys
import json

def solve(task: dict) -> dict:
    print('Solving', task['question'], file=sys.stderr)
    return {
        # eval is evil, but it's only an example :)
        'value': eval(task['question']),
    }

lines = sys.stdin
lines = map(lambda line: line.strip(), lines)
lines = filter(lambda line: line != '', lines)
tasks = map(json.loads, lines)
results = map(solve, tasks)

for result in results:
    print(json.dumps(result), flush=True)
