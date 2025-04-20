import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="profile-container">
      <h1>Личный кабинет</h1>
      <p>Здесь будет отображаться информация о пользователе и его списки книг</p>
    </div>
  `,
  styles: [`
    .profile-container {
      padding: 20px;
      max-width: 1200px;
      margin: 0 auto;
    }
  `]
})
export class ProfileComponent {} 