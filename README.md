# Task dispatcher

Task dispatcher is a simple tool for dispatching tasks for many clients. It consists of two elements:
- server
- client

First, you have to prepare some tasks and save them into a JSONL file. A task is represented by a single line. Internal format of task is not restricted, although it must be sufficient to be properly understood by a client. Example file `data.jsonl`:
```json
{ "question": "2 + 2" }
{ "question": "3 * 7" }
{ "question": "100 - 25" }
```

Next, you use a command to load the data to the server's database:
```sh
php bin/console app:task:import data.jsonl
```

Then, generate some clients credentials:
```sh
php bin/console app:user:create --name "My PC"
php bin/console app:user:create --name "My brother's PC"
php bin/console app:user:create --name "My grandmother's laptop"
```

After performing each command, you'll get a client api token. You can list all the client's with their credentials by running:
```sh
php bin/console app:user:list
```

Now, prepare a client executor script. Executor takes JSON tasks from stdin, line by line, and output result JSONs. Remember to flush stdout after printing current task solution! As an example, you can use `example_executor.py` script, changing the definition of solve function.

Finally, copy `task_dispatcher.py` client and your executor to each machine, set up `API_URL` and `API_KEY` environment variables, and run the dispatcher:
```sh
./task_dispatcher.py -l -- ./example_executor.py
```

Expression `--` separates task dispatcher options and executor command. Option `-l` cause that dispatcher will be running until any task is available to solve. Without this option, dispatcher would solve only one task.

To check progress of tasks solving, try the following commands:
```sh
php bin/console app:task:stats
php bin/console app:user:stats
```

To export results, after tasks will be solved, use:
```sh
php bin/console app:task:export | tee results.jsonl
```
