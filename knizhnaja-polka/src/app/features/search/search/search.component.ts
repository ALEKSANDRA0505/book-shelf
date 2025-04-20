import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { Book } from '../../../core/models/book.model';
import { User } from '../../../core/models/user.model';
import { Writer } from '../../../core/models/writer.model';
import { ActivatedRoute } from '@angular/router';
import { SearchService } from '../../../core/services/search.service';
interface SearchResults {
  books: Book[];
  authors: User[];
  writers: Writer[];
  genres: any[];
}
@Component({
  selector: 'app-search',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './search.component.html',
  styleUrl: './search.component.css'
})
export class SearchComponent implements OnInit {
  searchQuery: string = '';
  public searchResults: SearchResults | null = null; 
  isLoading: boolean = false;
  errorMessage: string | null = null;
  allBooks: any[] = [];
  filteredBooks: any[] = [];
  allAuthors: any[] = [];
  filteredAuthors: any[] = [];
  allWriters: any[] = [];
  filteredWriters: any[] = [];
  selectedCategories = {
    books: true,
    authors: true,
    writers: true
  };
  sortType = 'relevance';
  constructor(private route: ActivatedRoute, private searchService: SearchService) { }
  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      this.searchQuery = params['query'] || '';
      if (this.searchQuery) {
        this.performSearch();
      }
    });
  }
  performSearch(): void {
    if (!this.searchQuery.trim()) {
      this.searchResults = null;
      return;
    }
    this.isLoading = true;
    this.errorMessage = null;
    this.searchResults = null;
    this.searchService.searchAll(this.searchQuery).subscribe({
      next: (results: any) => {
        this.searchResults = {
          books: results.books || [],
          authors: results.authors || [],
          writers: results.writers || [],
          genres: results.genres || []
        };
        console.log('Результаты поиска:', this.searchResults);
        this.isLoading = false;
      },
      error: (err: any) => {
        console.error('Ошибка поиска:', err);
        this.errorMessage = 'Произошла ошибка при поиске. Пожалуйста, попробуйте позже.';
        this.isLoading = false;
      }
    });
  }
  getStarClasses(rating: number | null | undefined): string[] {
    if (rating === null || rating === undefined) return [];
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5 ? 1 : 0;
    const emptyStars = 5 - fullStars - halfStar;
    
    let stars: string[] = [];
    for(let i=0; i<fullStars; i++) stars.push('fas fa-star');
    if(halfStar) stars.push('fas fa-star-half-alt');
    for(let i=0; i<emptyStars; i++) stars.push('far fa-star');
    
    return stars;
  }
  
  getWritersString(writers: Writer[] | undefined): string {
      if (!writers || writers.length === 0) {
          return 'Автор неизвестен';
      }
      return writers.map(w => w.name).join(', ');
  }
}
