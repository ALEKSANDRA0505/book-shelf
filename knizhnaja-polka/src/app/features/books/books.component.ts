import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
@Component({
  selector: 'app-books',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="books-container">
      <h1>Обширная база книг</h1>
      <p>Здесь будет отображаться список всех доступных книг</p>
    </div>
  `,
  styles: [`
    .books-container {
      padding: 20px;
      max-width: 1200px;
      margin: 0 auto;
    }
  `]
})
export class BooksComponent {} 