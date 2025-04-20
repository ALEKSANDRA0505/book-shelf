import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
@Component({
  selector: 'app-authors',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="authors-container">
      <h1>Подписки на авторов рецензий</h1>
      <p>Здесь будет отображаться список авторов рецензий</p>
    </div>
  `,
  styles: [`
    .authors-container {
      padding: 20px;
      max-width: 1200px;
      margin: 0 auto;
    }
  `]
})
export class AuthorsComponent {} 