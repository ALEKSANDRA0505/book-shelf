import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { SearchService } from '../../../core/services/search.service';
@Component({
  selector: 'app-search-bar',
  standalone: true,
  imports: [FormsModule],
  templateUrl: './search-bar.component.html',
  styleUrl: './search-bar.component.css'
})
export class SearchBarComponent {
  searchQuery: string = '';
  constructor(private searchService: SearchService) {}
  onSearch() {
    this.searchService.performSearch(this.searchQuery);
  }
  onKeyPress(event: KeyboardEvent) {
    if (event.key === 'Enter') {
      event.preventDefault();
      this.onSearch();
    }
  }
}