import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { UserService } from '../../../core/services/user.service';
import { User } from '../../../core/models/user.model';
interface AuthorDisplay {
  id: number;
  name: string;
  avatarUrl: string | null | undefined;
}
@Component({
  selector: 'app-authors-list',
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './authors-list.component.html',
  styleUrl: './authors-list.component.css',
  standalone: true
})
export class AuthorsListComponent implements OnInit {
  authors: AuthorDisplay[] = [];
  filteredAuthors: AuthorDisplay[] = [];
  searchTerm: string = '';
  isLoading: boolean = false;
  error: string | null = null;
  constructor(private userService: UserService) {}
  ngOnInit() {
    this.loadAuthors();
  }
  loadAuthors(): void {
    this.isLoading = true;
    this.error = null;
    this.userService.getAuthors().subscribe({
      next: (users: User[]) => {
        this.authors = users.map(user => ({
          id: user.id_user,
          name: user.username || 'Без имени',
          avatarUrl: user.profile_picture_url || 'assets/img/default-avatar.png'
        }));
        this.filterAuthors();
        this.isLoading = false;
        console.log('Authors (Users) loaded:', this.authors);
      },
      error: (err) => {
        console.error('Error loading authors:', err);
        this.error = 'Не удалось загрузить список авторов.';
        this.isLoading = false;
      }
    });
  }
  filterAuthors() {
    if (!this.searchTerm.trim()) {
      this.filteredAuthors = [...this.authors];
      return;
    }
    
    const searchTermLower = this.searchTerm.toLowerCase().trim();
    this.filteredAuthors = this.authors.filter(author => 
      author.name.toLowerCase().includes(searchTermLower)
    );
  }
}
