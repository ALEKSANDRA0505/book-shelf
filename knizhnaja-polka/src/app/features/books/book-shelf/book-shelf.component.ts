import { Component, OnInit, ElementRef, ViewChild, HostListener, ChangeDetectorRef } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { FaIconLibrary } from '@fortawesome/angular-fontawesome';
import { faArrowLeft, faArrowRight } from '@fortawesome/free-solid-svg-icons';
import { Genre } from '../../../core/models/genre.model';
import { Book } from '../../../core/models/book.model';
import { GenreService } from '../../../core/services/genre.service';
import { BookService } from '../../../core/services/book.service';
import { Writer } from '../../../core/models/writer.model';
import { Subscription } from 'rxjs';
@Component({
  selector: 'app-polka',
  templateUrl: './book-shelf.component.html',
  styleUrls: ['./book-shelf.component.css'],
  standalone: true,
  imports: [RouterModule, CommonModule, FontAwesomeModule]
})
export class PolkaComponent implements OnInit {
  @ViewChild('genreContainer') genreContainer!: ElementRef;
  
  genres: Genre[] = [];
  isLoadingGenres: boolean = false;
  genresError: string | null = null;
  allBooks: Book[] = [];
  randomBooks: Book[] = [];
  isLoadingBooks: boolean = false;
  booksError: string | null = null;
  faArrowLeft = faArrowLeft;
  faArrowRight = faArrowRight;
  showLeftArrow = false;
  showRightArrow = true;
  isScrolling = false;
  private genresSubscription: Subscription | null = null;
  private booksSubscription: Subscription | null = null;
  constructor(
    private router: Router, 
    private renderer: FaIconLibrary,
    private genreService: GenreService,
    private bookService: BookService,
    private cdRef: ChangeDetectorRef
  ) {
    this.renderer.addIcons(faArrowLeft, faArrowRight);
  }
  ngOnInit(): void {
    this.loadGenres();
    this.loadBooks();
    setTimeout(() => this.checkArrows(), 100);
  }
  ngOnDestroy(): void {
    this.genresSubscription?.unsubscribe();
    this.booksSubscription?.unsubscribe();
  }
  loadGenres(): void {
    console.log('Загрузка жанров из API...');
    this.isLoadingGenres = true;
    this.genresError = null;
    this.genresSubscription?.unsubscribe();
    this.genresSubscription = this.genreService.getGenres().subscribe({
      next: (loadedGenres) => {
        const genreImages: { [key: string]: string } = {
          'Фантастика': 'assets/img/genre1.jpg',
          'Детективы': 'assets/img/genre2.jpg',
          'Романы': 'assets/img/romance.jpg',
          'Поэзия': 'assets/img/poetry.jpg',
          'Научпоп': 'assets/img/science.jpg',
          'История': 'assets/img/history.jpg',
          'Приключения': 'assets/img/adventure.jpg',
          'Ужасы': 'assets/img/horror.jpg',
          'Классическая литература': 'assets/img/classic.jpg'
        };
        this.genres = loadedGenres.map(genre => ({
          ...genre,
          image: genreImages[genre.name] || 'assets/img/placeholder_genre.jpg' 
        }));
        
        console.log('Жанры загружены:', this.genres);
        this.isLoadingGenres = false;
        setTimeout(() => this.checkArrows(), 0);
        this.cdRef.detectChanges();
      },
      error: (error) => {
        console.error('Ошибка загрузки жанров:', error);
        this.genresError = 'Не удалось загрузить жанры.';
        this.isLoadingGenres = false;
      }
    });
  }
  loadBooks(): void {
    console.log('Загрузка книг из API...');
    this.isLoadingBooks = true;
    this.booksError = null;
    this.booksSubscription?.unsubscribe();
    this.booksSubscription = this.bookService.getBooks().subscribe({
      next: (loadedBooks) => {
        this.allBooks = loadedBooks;
        console.log('Все книги загружены:', this.allBooks);
        this.selectRandomBooks();
        this.isLoadingBooks = false;
        this.cdRef.detectChanges();
      },
      error: (error) => {
        console.error('Ошибка загрузки книг:', error);
        this.booksError = 'Не удалось загрузить книги.';
        this.isLoadingBooks = false;
      }
    });
  }
  selectRandomBooks(): void {
    if (!this.allBooks || this.allBooks.length === 0) {
      this.randomBooks = [];
      return;
    }
    const shuffled = [...this.allBooks];
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    this.randomBooks = shuffled.slice(0, 5);
    console.log('Случайные 5 книг:', this.randomBooks);
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
  scrollGenres(amount: number): void {
    if (this.isScrolling) return;
    
    this.isScrolling = true;
    console.log('Scrolling genres by', amount);
    
    const container = this.genreContainer.nativeElement;
    container.scrollBy({ left: amount, behavior: 'smooth' });
    
    setTimeout(() => {
      this.checkArrows();
      this.isScrolling = false;
    }, 300);
  }
  @HostListener('window:resize', ['$event'])
  onResize(event: Event): void {
    this.checkArrows();
  }
  checkArrows(): void {
    if (this.genreContainer) {
      const container = this.genreContainer.nativeElement;
      this.showLeftArrow = container.scrollLeft > 0;
      this.showRightArrow = container.scrollWidth > container.clientWidth && 
                            container.scrollLeft < (container.scrollWidth - container.clientWidth - 1);
    } else {
      this.showLeftArrow = false;
      this.showRightArrow = true;
    }
  }
  goToProfile() {
    this.router.navigate(['/profil']);
  }
}