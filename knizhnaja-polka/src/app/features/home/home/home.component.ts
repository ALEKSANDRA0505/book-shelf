import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { BookService } from '../../../core/services/book.service';
import { Book } from '../../../core/models/book.model';
import { Writer } from '../../../core/models/writer.model';
import { Subscription } from 'rxjs';
@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent implements OnInit, OnDestroy {
  currentSlide = 0;
  currentBookSlide = 0;
  slides = [
    {
      title: 'Чат для книголюбов',
      description: 'Обсуждай прочитанное, делись мнением и получай рекомендации',
      buttonText: 'Перейти в чат',
      route: '/chat'
    },
    {
      title: 'Создай свою полку',
      description: 'Добавляй книги в виш-лист, чтобы не забыть прочитать',
      buttonText: 'Перейти в профиль',
      route: '/profil'
    },
    {
      title: 'Начни писать рецензии',
      description: 'Оставь свой след в истории книжной культуры сайта',
      buttonText: 'Написать рецензию',
      route: '/review-list'
    },
    {
      title: 'Подписки на авторов рецензий',
      description: 'Следи за обновлениями любимых рецензентов',
      buttonText: 'Перейти',
      route: '/authors-list'
    },
    {
      title: 'Личный кабинет',
      description: 'Создавай списки избранных книг и отслеживай свою историю чтения',
      buttonText: 'Перейти',
      route: '/auth/sign-in'
    }
  ];
  popularBooks: Book[] = [];
  private slideInterval: any;
  bookSlides: Book[][] = [];
  booksPerSlide = 4;
  isLoading: boolean = false;
  errorMessage: string | null = null;
  private booksSubscription: Subscription | null = null;
  constructor(private bookService: BookService) { }
  ngOnInit() {
    this.startSlideShow();
    this.loadPopularBooks();
  }
  ngOnDestroy() {
    if (this.slideInterval) {
      clearInterval(this.slideInterval);
    }
    this.booksSubscription?.unsubscribe();
  }
  loadPopularBooks(): void {
    this.isLoading = true;
    this.errorMessage = null;
    this.popularBooks = [];
    this.bookSlides = [];
    this.booksSubscription = this.bookService.getPopularBooks().subscribe({
      next: (books) => {
        this.popularBooks = books;
        this.splitBooksIntoSlides();
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Ошибка загрузки популярных книг:', error);
        this.errorMessage = 'Не удалось загрузить популярные книги.';
        this.isLoading = false;
      }
    });
  }
  splitBooksIntoSlides(): void {
    this.bookSlides = [];
    if (!this.popularBooks || this.popularBooks.length === 0) {
      return;
    }
    for (let i = 0; i < this.popularBooks.length; i += this.booksPerSlide) {
      this.bookSlides.push(this.popularBooks.slice(i, i + this.booksPerSlide));
    }
    this.currentBookSlide = 0;
  }
  startSlideShow(): void {
    this.slideInterval = setInterval(() => {
      this.nextSlide();
    }, 5000);
  }
  nextSlide() {
    this.currentSlide = (this.currentSlide + 1) % this.slides.length;
  }
  prevSlide() {
    this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
  }
  nextBookSlide() {
    if (this.bookSlides.length > 0) {
      this.currentBookSlide = (this.currentBookSlide + 1) % this.bookSlides.length;
    }
  }
  prevBookSlide() {
    if (this.bookSlides.length > 0) {
      this.currentBookSlide = (this.currentBookSlide - 1 + this.bookSlides.length) % this.bookSlides.length;
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
    const writerNames = writers.map(w => w.name);
    if (writerNames.length > 2) {
      return writerNames.slice(0, 2).join(', ') + ' и др.';
    }
    return writerNames.join(', ');
  }
}
