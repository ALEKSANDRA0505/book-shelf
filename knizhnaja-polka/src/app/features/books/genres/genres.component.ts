import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { Genre } from '../../../core/models/genre.model';
import { Book } from '../../../core/models/book.model';
import { GenreService } from '../../../core/services/genre.service';
import { BookService } from '../../../core/services/book.service';
import { catchError, map, switchMap } from 'rxjs/operators';
import { of, Observable } from 'rxjs';
import { Writer } from '../../../core/models/writer.model';
@Component({
  selector: 'app-genres',
  imports: [CommonModule, RouterModule],
  templateUrl: './genres.component.html',
  styleUrl: './genres.component.css',
  standalone: true
})
export class GenresComponent implements OnInit {
  books: Book[] = [];
  genre: Genre | null = null;
  genreId: number | null = null;
  shelves: Book[][] = [];
  isLoadingGenre: boolean = false;
  isLoadingBooks: boolean = false;
  errorMessage: string | null = null;
  
  constructor(
    private route: ActivatedRoute,
    private genreService: GenreService,
    private bookService: BookService
  ) {}
  
  ngOnInit() {
    this.isLoadingGenre = true;
    this.errorMessage = null;
    this.genre = null;
    this.genreId = null;
    this.route.paramMap.pipe(
      map(params => params.get('slug')),
      switchMap(slug => {
        if (slug) {
          return this.genreService.getGenreBySlug(slug).pipe(
            catchError(error => {
              console.error('Ошибка загрузки жанра по слагу:', error);
              this.errorMessage = 'Не удалось загрузить информацию о жанре.';
              this.isLoadingGenre = false;
              return of(null);
            })
          );
        } else {
          this.errorMessage = 'Слаг жанра не найден в URL.';
          this.isLoadingGenre = false;
          return of(null);
        }
      })
    ).subscribe(loadedGenre => {
      this.genre = loadedGenre;
      this.isLoadingGenre = false;
      if (this.genre) {
        this.genreId = this.genre.id_genre;
        this.loadBooksByGenre(this.genre.id_genre);
      } else {
        this.books = [];
        this.shelves = [];
      }
    });
  }
  
  loadBooksByGenre(genreId: number): void {
    this.isLoadingBooks = true;
    this.errorMessage = null;
    this.books = [];
    this.shelves = [];
    this.bookService.getBooks()
      .pipe(
        map((allBooks: Book[]) => {
          return allBooks.filter(book =>
            book.genres?.some(g => g.id_genre === genreId)
          );
        }),
        catchError(error => {
          console.error('Ошибка загрузки книг:', error);
          this.errorMessage = 'Не удалось загрузить книги для этого жанра.';
          this.isLoadingBooks = false;
          return of([]);
        })
      )
      .subscribe((filteredBooks: Book[]) => {
        this.books = filteredBooks;
        this.organizeBooksIntoShelves();
        this.isLoadingBooks = false;
        console.log('Книги для жанра загружены:', this.books);
      });
  }
  
  organizeBooksIntoShelves(): void {
    const booksPerShelf = 6;
    this.shelves = [];
    for (let i = 0; i < this.books.length; i += booksPerShelf) {
      this.shelves.push(this.books.slice(i, i + booksPerShelf));
    }
  }
  
  getStars(rating: number | null | undefined): number[] {
    rating = rating ?? 0;
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5 ? 0.5 : 0;
    const emptyStars = 5 - fullStars - (halfStar === 0.5 ? 1 : 0);
    let stars = [];
    for (let i = 0; i < fullStars; i++) stars.push(1);
    if (halfStar === 0.5) stars.push(0.5);
    for (let i = 0; i < emptyStars; i++) stars.push(0);
    while(stars.length < 5) stars.push(0);
    return stars;
  }
  getWritersString(writers: Writer[] | undefined): string {
    if (!writers || writers.length === 0) {
      return 'Автор неизвестен';
    }
    return writers.map(w => w.name).join(', ');
  }
}
