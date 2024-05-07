// src/app/task/task.component.ts
import { Component } from '@angular/core';
import { Task } from '../models/task.model';
import { TaskService } from '../services/task.service';

@Component({
  selector: 'app-task',
  templateUrl: './task.component.html',
  styleUrls: ['./task.component.css']
})
export class TaskComponent {
  tasks: Task[] = [];
  newTaskDescription: string = '';
  newTaskDeadline: string = '';

  filter: 'all' | 'completed' | 'active' = 'all';

  constructor(private taskService: TaskService) {}

  ngOnInit(): void {
    this.loadTasks();
  }

  loadTasks(): void {
    this.taskService.getTasks().subscribe((tasks: Task[]) => {
      this.tasks = tasks;
    });
  }

  addTask(): void {
    const newTask: Task = {
      id: this.tasks.length,
      description: this.newTaskDescription,
      deadline: this.newTaskDeadline,
      completed: false
    };

    this.taskService.createTask(newTask).subscribe((task: Task) => {
      this.tasks.push(newTask);
      console.log('tasks', this.tasks);
      this.newTaskDescription = '';
      this.newTaskDeadline = '';
    });
  }

  completeTask(task: Task): void {
    task.completed = !task.completed;
    this.taskService.updateTask(task).subscribe(() => {
      this.tasks.sort((a, b) => a.completed === b.completed ? 0 : a.completed ? 1 : -1);
    });
  }

  deleteTask(taskId: number, index: number): void {
    this.taskService.deleteTask(taskId).subscribe(() => {
      this.tasks.splice(index, 1);
    });
  }

  get tasksFiltered(): Task[] {
    if (this.filter === 'all') {
      return this.tasks;
    }
    return this.tasks.filter(task => this.filter === 'completed' ? task.completed : !task.completed);
  }

}
