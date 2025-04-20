import { Component, OnInit, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { Book } from '../../../core/models/book.model';
import { BookService } from '../../../core/services/book.service';
import { catchError } from 'rxjs/operators';
import { of } from 'rxjs';
import { Writer } from '../../../core/models/writer.model';
@Component({
  selector: 'app-all-books',
  imports: [CommonModule, RouterModule],
  templateUrl: './all-books.component.html',
  styleUrl: './all-books.component.css',
  standalone: true
})
export class AllBooksComponent implements OnInit {
  books: Book[] = [];
  shelves: any[][] = [];
  booksPerShelf: number = 6;
  isLoading: boolean = false;
  errorMessage: string | null = null;
  
  constructor(private bookService: BookService) { }
  ngOnInit(): void {
    this.loadBooks();
  }
  loadBooks(): void {
    this.isLoading = true;
    this.errorMessage = null;
    this.books = [];
    this.shelves = [];
    this.bookService.getBooks()
      .pipe(
        catchError(error => {
          console.error('Ошибка при загрузке книг:', error);
          this.errorMessage = 'Не удалось загрузить книги. Пожалуйста, попробуйте позже.';
          this.isLoading = false;
          return of([]);
        })
      )
      .subscribe((loadedBooks: Book[]) => {
        this.books = loadedBooks;
        this.calculateBooksPerShelf();
        this.organizeBooks();
        this.isLoading = false;
        console.log('Книги загружены:', this.books);
      });
  }
  @HostListener('window:resize')
  onResize() {
    this.calculateBooksPerShelf();
    this.organizeBooks();
  }
  calculateBooksPerShelf() {
    const windowWidth = window.innerWidth;
    if (windowWidth < 768) {
      this.booksPerShelf = 2;
    } else if (windowWidth < 992) {
      this.booksPerShelf = 3;
    } else if (windowWidth < 1200) {
      this.booksPerShelf = 4;
    } else {
      this.booksPerShelf = 6;
    }
  }
  organizeBooks() {
    this.shelves = [];
    const booksToDisplay = this.books;
    for (let i = 0; i < booksToDisplay.length; i += this.booksPerShelf) {
      this.shelves.push(booksToDisplay.slice(i, i + this.booksPerShelf));
    }
  }
  getStars(rating: number | null | undefined): string[] {
    rating = rating ?? 0;
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;
    const stars: string[] = [];
    for (let i = 0; i < fullStars; i++) stars.push('fas fa-star');
    if (halfStar) stars.push('fas fa-star-half-alt');
    while (stars.length < 5) stars.push('far fa-star');
    return stars;
  }
  getWritersString(writers: Writer[] | undefined): string {
    if (!writers || writers.length === 0) {
      return 'Автор неизвестен';
    }
    return writers.map(w => w.name).join(', ');
  }
}
