from task_dispatcher import TaskDispatcher, Task, ApiException, NoTasksAvailableException

try:
    dispacher = TaskDispatcher(api_url='http://localhost', api_key='c35f89759bc8e924ebd85343687f52e5')
    with Task.reserve(dispacher) as task:
        print(task.params())
        #task.add_result(json_data={ 'a': 1 })
        #task.add_result(stream=open('/home/eryk/polska.mp4', 'rb'))
        task.add_result(content='Ala ma kota', mime_type='text/plain')
        task.finish()
except NoTasksAvailableException:
    pass
except ApiException as e:
    print(e)
