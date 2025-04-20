import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { Subscription, Observable } from 'rxjs';
import { switchMap, tap } from 'rxjs/operators';
import { SearchService, SearchResults } from '../../core/services/search.service';
import { Writer } from '../../core/models/writer.model';
import { Genre } from '../../core/models/genre.model';
interface Book {
  id: number;
  title: string;
  author: string;
  rating: number;
  cover: string;
}
interface Person {
  id: number;
  name: string;
  role: string;
  avatar: string;
}
@Component({
  selector: 'app-search',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './search/search.component.html',
  styleUrls: ['./search/search.component.css']
})
export class SearchComponent implements OnInit, OnDestroy {
  searchQuery: string = '';
  sortType: string = 'relevance';
  
  selectedCategories = {
    books: true,
    authors: true,
    writers: true
  };
  
  books: Book[] = [
    { id: 1, title: 'Война и мир', author: 'Лев Толстой', rating: 4.8, cover: 'assets/img/book1.jpg' },
    { id: 2, title: 'Преступление и наказание', author: 'Фёдор Достоевский', rating: 4.9, cover: 'assets/img/book2.jpg' },
    { id: 3, title: 'Мастер и Маргарита', author: 'Михаил Булгаков', rating: 4.7, cover: 'assets/img/book3.jpg' },
    { id: 4, title: '1984', author: 'Джордж Оруэлл', rating: 4.6, cover: 'assets/img/book4.jpg' },
    { id: 5, title: 'Алгоритмы: построение и анализ', author: 'Томас Кормен', rating: 4.5, cover: 'assets/img/book5.jpg' }
  ];
  
  authors: Person[] = [
    { id: 1, name: 'Анна Иванова', role: 'Автор рецензий', avatar: 'assets/img/author1.jpg' },
    { id: 2, name: 'Петр Сидоров', role: 'Автор рецензий', avatar: 'assets/img/author2.jpg' },
    { id: 3, name: 'Мария Петрова', role: 'Автор рецензий', avatar: 'assets/img/author3.jpg' }
  ];
  
  writers: Person[] = [
    { id: 1, name: 'Лев Толстой', role: 'Писатель', avatar: 'assets/img/writer1.jpg' },
    { id: 2, name: 'Фёдор Достоевский', role: 'Писатель', avatar: 'assets/img/writer2.jpg' },
    { id: 3, name: 'Михаил Булгаков', role: 'Писатель', avatar: 'assets/img/writer3.jpg' }
  ];
  
  filteredBooks: Book[] = [];
  filteredAuthors: Person[] = [];
  filteredWriters: Person[] = [];
  
  searchResults: SearchResults | null = null;
  isLoading: boolean = false;
  errorMessage: string | null = null;
  private routeSub: Subscription | null = null;
  constructor(
    private route: ActivatedRoute,
    private searchService: SearchService
  ) {}
  
  ngOnInit(): void {
    this.routeSub = this.route.queryParamMap.pipe(
      tap(params => {
        this.searchQuery = params.get('query') || '';
        this.searchResults = null;
        this.isLoading = true;
        this.errorMessage = null;
        console.log('Search query:', this.searchQuery);
      }),
      switchMap(params => {
        const query = params.get('query');
        if (query && query.trim()) {
          return this.searchService.searchDatabase(query);
        } else {
          return new Observable<SearchResults>(observer => { 
            observer.next({ books: [], writers: [], genres: [], authors: [] }); 
            observer.complete();
          });
        }
      })
    ).subscribe({
      next: (results) => {
        this.searchResults = results;
        this.isLoading = false;
        console.log('Search results:', results);
      },
      error: (err) => {
        console.error('Search error:', err);
        this.errorMessage = 'Произошла ошибка во время поиска.';
        this.isLoading = false;
      }
    });
  }
  ngOnDestroy(): void {
    this.routeSub?.unsubscribe();
  }
  
  performSearch() {
    const query = this.searchQuery.trim().toLowerCase();
    
    if (query === '') {
      this.showAllResults();
      return;
    }
    
    this.filteredBooks = this.books.filter(book => 
      book.title.toLowerCase().includes(query) || 
      book.author.toLowerCase().includes(query)
    );
    
    this.filteredAuthors = this.authors.filter(author => 
      author.name.toLowerCase().includes(query)
    );
    
    this.filteredWriters = this.writers.filter(writer => 
      writer.name.toLowerCase().includes(query)
    );
    
    this.sortResults();
  }
  
  filterResults() {
  }
  
  sortResults() {
    if (this.sortType === 'rating') {
      this.filteredBooks.sort((a, b) => b.rating - a.rating);
    } else if (this.sortType === 'name') {
      this.filteredBooks.sort((a, b) => a.title.localeCompare(b.title));
      
      this.filteredAuthors.sort((a, b) => a.name.localeCompare(b.name));
      this.filteredWriters.sort((a, b) => a.name.localeCompare(b.name));
    }
  }
  
  showAllResults() {
    this.filteredBooks = [...this.books];
    this.filteredAuthors = [...this.authors];
    this.filteredWriters = [...this.writers];
    
    this.sortResults();
  }
  
  hasAnyResults(): boolean {
    return (
      (this.selectedCategories.books && this.filteredBooks.length > 0) ||
      (this.selectedCategories.authors && this.filteredAuthors.length > 0) ||
      (this.selectedCategories.writers && this.filteredWriters.length > 0)
    );
  }
  
  hasNoResults(): boolean {
    return !this.hasAnyResults();
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