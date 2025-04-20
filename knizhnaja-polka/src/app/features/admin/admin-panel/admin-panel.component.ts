import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { User } from '../../../core/models/user.model';
import { UserService } from '../../../core/services/user.service';
import { Book } from '../../../core/models/book.model';
import { BookService } from '../../../core/services/book.service';
import { Writer } from '../../../core/models/writer.model'; 
import { Genre } from '../../../core/models/genre.model'; 
import { Review } from '../../../core/models/review.model'; 
import { Achievement } from '../../../core/models/achievement.model'; 
import { WriterService } from '../../../core/services/writer.service';
import { ReviewService } from '../../../core/services/review.service';
import { Router } from '@angular/router';
import { ChangeDetectorRef } from '@angular/core';
import { Observable } from 'rxjs';
@Component({
  selector: 'app-admin-panel',
  templateUrl: './admin-panel.component.html',
  styleUrls: ['./admin-panel.component.css'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule
  ]
})
export class AdminPanelComponent implements OnInit {
  currentSection: string = 'users';
  users: User[] = [];
  books: Book[] = [];
  writers: Writer[] = [];
  genres: Genre[] = [];
  reviews: Review[] = [];
  achievements: Achievement[] = [];
  filteredUsers: User[] = [];
  filteredBooks: Book[] = [];
  filteredWriters: Writer[] = [];
  filteredGenres: Genre[] = [];
  filteredReviews: Review[] = [];
  filteredAchievements: Achievement[] = [];
  userSearchTerm: string = '';
  bookSearchTerm: string = '';
  writerSearchTerm: string = '';
  genreSearchTerm: string = '';
  reviewSearchTerm: string = '';
  achievementSearchTerm: string = '';
  isLoadingUsers: boolean = false;
  errorUsers: string | null = null;
  isLoadingBooks: boolean = false;
  errorBooks: string | null = null;
  isLoadingWriters: boolean = false;
  errorWriters: string | null = null;
  isLoadingReviews: boolean = false;
  errorReviews: string | null = null;
  editingUser: User | null = null;
  isEditUserModalVisible: boolean = false;
  updateError: string | null = null;
  newUser: Partial<User> = {};
  isCreateUserModalVisible: boolean = false;
  createError: string | null = null;
  editingBook: Book | null = null;
  isEditBookModalVisible: boolean = false;
  updateBookError: string | null = null;
  newBook: Partial<Book> = {};
  isCreateBookModalVisible: boolean = false;
  createBookError: string | null = null;
  editingWriter: Writer | null = null;
  isEditWriterModalVisible: boolean = false;
  updateWriterError: string | null = null;
  newWriter: Partial<Writer> = {};
  isCreateWriterModalVisible: boolean = false;
  createWriterError: string | null = null;
  constructor(
    private userService: UserService,
    private writerService: WriterService,
    private bookService: BookService,
    private reviewService: ReviewService,
    private router: Router,
    private cdr: ChangeDetectorRef
  ) { }
  ngOnInit(): void {
    this.loadData(this.currentSection);
  }
  selectSection(section: string): void {
    this.currentSection = section;
    if ((section === 'users' && this.users.length === 0) || 
        (section === 'books' && this.books.length === 0) ||
        (section === 'writers' && this.writers.length === 0)) {
       this.loadData(section);
    }
    this.applyFilters(section); 
  }
  loadData(section: string): void {
    console.log(`Загрузка данных для раздела: ${section}`);
    switch (section) {
      case 'users':
        this.isLoadingUsers = true;
        this.errorUsers = null;
        this.userService.getUsers().subscribe({
          next: (data) => {
            this.users = data;
            this.applyFilters('users');
            this.isLoadingUsers = false;
          },
          error: (err) => {
            console.error('Error loading users:', err);
            this.errorUsers = err.error?.error || `Не удалось загрузить пользователей: ${err.message}`;
            this.isLoadingUsers = false;
          }
        });
        break;
      case 'books':
        this.isLoadingBooks = true;
        this.errorBooks = null;
        this.bookService.getBooks().subscribe({ 
          next: (data) => {
            this.books = data;
            this.applyFilters('books');
            this.isLoadingBooks = false; 
            console.log('Books loaded:', this.books); 
          },
          error: (err) => {
            console.error('Error loading books:', err);
            this.errorBooks = err.error?.error || `Не удалось загрузить книги: ${err.message}`;
            this.isLoadingBooks = false; 
          }
        });
        break;
      case 'writers':
        this.isLoadingWriters = true;
        this.errorWriters = null;
        this.writerService.getWriters().subscribe({
          next: (data) => {
            this.writers = data;
            this.applyFilters('writers');
            this.isLoadingWriters = false;
          },
          error: (err) => {
            console.error('Error loading writers:', err);
            this.errorWriters = err.error?.error || `Не удалось загрузить писателей: ${err.message}`;
            this.isLoadingWriters = false;
          }
        });
        break;
      /*
      case 'genres':
        this.genres = [...];
        this.applyFilters('genres');
        break;
      case 'reviews':
        this.reviews = [...];
        this.applyFilters('reviews');
        break;
      case 'achievements':
        this.achievements = [...];
        this.applyFilters('achievements');
        break;
      */
      default:
        console.warn(`Загрузка данных для раздела ${section} пока не реализована.`);
        break;
    }
  }
  applyFilters(section: string = this.currentSection): void {
    console.log(`Применение фильтра для: ${section}`);
    switch (section) {
      case 'users':
        const userTerm = this.userSearchTerm.toLowerCase();
        this.filteredUsers = this.users.filter(u =>
          u.username.toLowerCase().includes(userTerm) ||
          u.email.toLowerCase().includes(userTerm) ||
          (u.city && u.city.toLowerCase().includes(userTerm)) ||
          (u.status && u.status.toLowerCase().includes(userTerm))
        );
        break;
      case 'books':
        const bookTerm = this.bookSearchTerm.toLowerCase();
        this.filteredBooks = this.books.filter(b => 
          b.title.toLowerCase().includes(bookTerm) ||
          (b.author_string && b.author_string.toLowerCase().includes(bookTerm))
        );
        break;
      case 'writers':
        const writerTerm = this.writerSearchTerm.toLowerCase();
        this.filteredWriters = this.writers.filter(w => w.name.toLowerCase().includes(writerTerm));
        break;
      case 'genres':
        const genreTerm = this.genreSearchTerm.toLowerCase();
        this.filteredGenres = this.genres.filter(g => g.name.toLowerCase().includes(genreTerm));
        break;
      case 'reviews':
        const reviewTerm = this.reviewSearchTerm.toLowerCase();
        this.filteredReviews = this.reviews.filter(r =>
           (r.review_text && r.review_text.toLowerCase().includes(reviewTerm)) || 
           r.id_user.toString().includes(reviewTerm) || 
           r.id_book.toString().includes(reviewTerm)
        );
        break;
      case 'achievements':
        const achievementTerm = this.achievementSearchTerm.toLowerCase();
        this.filteredAchievements = this.achievements.filter(a => a.name.toLowerCase().includes(achievementTerm));
        break;
    }
  }
  addItem(type: string): void {
    if (type === 'user') {
      this.openCreateUserModal();
    } else if (type === 'book') {
      this.openCreateBookModal();
    } else if (type === 'writer') {
      this.openCreateWriterModal();
    } else {
      console.log(`Добавить новый элемент типа: ${type}`);
      alert(`Функционал добавления для типа '${type}' пока не реализован.`);
    }
  }
  editItem(item: any, type: string): void {
    if (type === 'user') {
       this.openEditUserModal(item as User);
    } else if (type === 'book') {
       this.openEditBookModal(item as Book);
    } else if (type === 'writer') {
       this.openEditWriterModal(item as Writer);
    } else {
        console.log(`Редактировать ${type}:`, item);
        alert(`Функционал редактирования для типа '${type}' пока не реализован.`);
    }
  }
  deleteItem(id: number, type: string): void {
    let confirmationMessage = '';
    let deleteObservable: Observable<any>;
    switch (type) {
      case 'user':
        confirmationMessage = 'Вы уверены, что хотите удалить этого пользователя?';
        if (!confirm(confirmationMessage)) return;
        this.userService.deleteUser(id).subscribe({
          next: () => {
            this.users = this.users.filter(u => u.id_user !== id);
            this.applyFilters('users');
            this.cdr.detectChanges();
            alert('Пользователь успешно удален.');
          },
          error: (err) => {
            console.error('Ошибка при удалении пользователя:', err);
            alert(`Ошибка при удалении пользователя: ${err.error?.message || err.message}`);
          }
        });
        break;
      case 'book':
        confirmationMessage = 'Вы уверены, что хотите удалить эту книгу?';
        console.log(`Удаление книги с ID: ${id}`);
        break;
      case 'writer':
        confirmationMessage = 'Вы уверены, что хотите удалить этого писателя?';
        if (!confirm(confirmationMessage)) return;
        this.writerService.deleteWriter(id).subscribe({
           next: (response) => {
             console.log('Ответ от deleteWriter:', response);
             this.writers = this.writers.filter(w => w.id_writer !== id);
             this.applyFilters('writers');
             this.cdr.detectChanges();
             alert('Писатель успешно удален.');
           },
           error: (err) => {
             console.error('Ошибка при удалении писателя:', err);
             alert(`Ошибка при удалении писателя: ${err.error?.message || err.message}`);
           }
        });
        break;
      default:
        console.warn(`Неизвестный тип для удаления: ${type}`);
    }
  }
  openEditUserModal(user: User): void {
    this.editingUser = { ...user }; 
    this.editingUser.password = ''; 
    this.isEditUserModalVisible = true;
    this.updateError = null;
  }
  closeEditUserModal(): void {
    this.isEditUserModalVisible = false;
    this.editingUser = null;
  }
  saveUserChanges(): void {
    if (!this.editingUser) return;
    console.log('Сохранение изменений для пользователя:', this.editingUser);
    this.updateError = null;
    const userId = this.editingUser.id_user;
    const userDataToUpdate: Partial<User & { password?: string }> = {
      username: this.editingUser.username,
      email: this.editingUser.email,
      status: this.editingUser.status,
      city: this.editingUser.city,
      age: this.editingUser.age,
      about_me: this.editingUser.about_me,
      reading_goal: this.editingUser.reading_goal,
    };
    if (this.editingUser.password && this.editingUser.password.trim() !== '') {
      userDataToUpdate.password = this.editingUser.password.trim();
    }
    this.userService.updateUser(userId, userDataToUpdate).subscribe({
      next: (response) => {
        console.log('User updated successfully:', response);
        alert('Пользователь успешно обновлен.');
        this.closeEditUserModal();
        this.loadData('users');

      },
      error: (err) => {
        console.error('Failed to update user:', err);
        this.updateError = err.error?.error || `Не удалось обновить пользователя: ${err.message}`;
      }
    });
  }
  openCreateUserModal(): void {
    this.newUser = { status: 'Читатель' };
    this.isCreateUserModalVisible = true;
    this.createError = null;
  }
  closeCreateUserModal(): void {
    this.isCreateUserModalVisible = false;
    this.newUser = {};
  }
  saveNewUser(): void {
    console.log('Попытка создания нового пользователя:', this.newUser);
    this.createError = null;
    if (!this.newUser.username || !this.newUser.email || !this.newUser.password || !this.newUser.status) {
        this.createError = 'Пожалуйста, заполните все обязательные поля: Имя пользователя, Email, Пароль, Статус.';
        console.error('Ошибка валидации при создании пользователя:', this.createError);
        return;
    }
    if (this.newUser.password.length < 6) {
        this.createError = 'Пароль должен содержать не менее 6 символов.';
        console.error('Ошибка валидации при создании пользователя:', this.createError);
        return;
    }
    this.userService.createUser(this.newUser).subscribe({
      next: (response) => {
        console.log('User created successfully:', response);
        alert(`Пользователь "${this.newUser.username}" успешно создан (ID: ${response.id_user}).`);
        this.closeCreateUserModal();
        this.loadData('users');
      },
      error: (err) => {
        console.error('Failed to create user:', err);
        this.createError = err.error?.error || `Не удалось создать пользователя: ${err.message}`;
      }
    });
  }
  openEditBookModal(book: Book): void {
    this.editingBook = { ...book };
    this.isEditBookModalVisible = true;
    this.updateBookError = null;
  }
  closeEditBookModal(): void {
    this.isEditBookModalVisible = false;
    this.editingBook = null;
  }
  saveBookChanges(): void {
    if (!this.editingBook) return;
    this.updateBookError = null;
    console.log('Сохранение изменений для книги:', this.editingBook);
    const { writers, genres, author_string, average_rating, review_count, ...bookDataToSend } = this.editingBook;
    
    this.bookService.updateBook(this.editingBook.id_book, bookDataToSend as Book & {id_book: number}).subscribe({
      next: (response) => {
        console.log('Book updated successfully:', response);
        alert('Книга успешно обновлена.');
        this.closeEditBookModal();
        this.loadData('books');
      },
      error: (err) => {
        console.error('Failed to update book:', err);
        this.updateBookError = err.error?.error || `Не удалось обновить книгу: ${err.message}`;
      }
    });
  }
  openCreateBookModal(): void {
    this.newBook = {};
    this.isCreateBookModalVisible = true;
    this.createBookError = null;
  }
  closeCreateBookModal(): void {
    this.isCreateBookModalVisible = false;
    this.newBook = {};
  }
  saveNewBook(): void {
    console.log('Создание новой книги:', this.newBook);
    this.createBookError = null;
    if (!this.newBook.title) {
        this.createBookError = 'Название книги обязательно для заполнения.';
        return;
    }
    
    this.bookService.createBook(this.newBook).subscribe({
      next: (response) => {
        console.log('Book created successfully:', response);
        alert(`Книга "${this.newBook.title}" успешно создана (ID: ${response.id_book}).`);
        this.closeCreateBookModal();
        this.loadData('books');
      },
      error: (err) => {
        console.error('Failed to create book:', err);
        this.createBookError = err.error?.error || `Не удалось создать книгу: ${err.message}`;
      }
    });
  }
  openEditWriterModal(writer: Writer): void {
    this.editingWriter = { ...writer };
    this.isEditWriterModalVisible = true;
    this.updateWriterError = null;
  }
  closeEditWriterModal(): void {
    this.isEditWriterModalVisible = false;
    this.editingWriter = null;
  }
  saveWriterChanges(): void {
    if (!this.editingWriter) return;
    this.updateWriterError = null;
    console.log('Сохранение изменений для писателя:', this.editingWriter);
    const writerDataToSend = {
      name: this.editingWriter.name,
      biography: this.editingWriter.biography || null, 
      photo_url: this.editingWriter.profile_picture_url || null
    };
    this.writerService.updateWriter(this.editingWriter.id_writer, writerDataToSend).subscribe({
      next: (response) => {
        console.log('Writer updated successfully:', response);
        alert('Писатель успешно обновлен.');
        this.closeEditWriterModal();
        this.loadData('writers');
      },
      error: (err) => {
        console.error('Failed to update writer:', err);
        this.updateWriterError = err.error?.error || `Не удалось обновить писателя: ${err.message}`;
      }
    });
  }
  openCreateWriterModal(): void {
    this.newWriter = {};
    this.isCreateWriterModalVisible = true;
    this.createWriterError = null;
  }
  closeCreateWriterModal(): void {
    this.isCreateWriterModalVisible = false;
    this.newWriter = {};
  }
  saveNewWriter(): void {
    console.log('Создание нового писателя:', this.newWriter);
    this.createWriterError = null;
    if (!this.newWriter.name) {
        this.createWriterError = 'Имя писателя обязательно для заполнения.';
        return;
    }
    
    const writerDataToSend = {
        name: this.newWriter.name,
        biography: this.newWriter.biography || null,
        photo_url: this.newWriter.profile_picture_url || null
    };
    this.writerService.createWriter(writerDataToSend).subscribe({
      next: (response) => {
        console.log('Writer created successfully:', response);
        alert(`Писатель "${this.newWriter.name}" успешно создан (ID: ${response.id_writer}).`);
        this.closeCreateWriterModal();
        this.loadData('writers');
      },
      error: (err) => {
        console.error('Failed to create writer:', err);
        this.createWriterError = err.error?.error || `Не удалось создать писателя: ${err.message}`;
      }
    });
  }
  getObjectKeys(obj: any): string[] {
    return Object.keys(obj);
  }
} 