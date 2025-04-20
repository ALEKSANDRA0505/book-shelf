import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { SearchService } from '../../../core/services/search.service';
import { AuthService } from '../../../core/services/auth.service';
import { Subscription } from 'rxjs';
@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './header.component.html',
  styleUrl: './header.component.css'
})
export class HeaderComponent implements OnInit, OnDestroy {
  searchQuery: string = '';
  isLoggedIn: boolean = false;
  private authSubscription: Subscription | null = null;
  constructor(
    private router: Router,
    private searchService: SearchService,
    private authService: AuthService
  ) {}
  ngOnInit(): void {
    this.authSubscription = this.authService.isLoggedIn$.subscribe(
      status => {
        this.isLoggedIn = status;
        console.log('HeaderComponent isLoggedIn status:', status);
      }
    );
  }
  ngOnDestroy(): void {
    if (this.authSubscription) {
      this.authSubscription.unsubscribe();
    }
  }
  navigateToLogin() {
    this.router.navigate(['/auth/sign-in']);
  }
  navigateToProfile() {
    this.router.navigate(['/user-profile']);
  }
  onSearch() {
    this.searchService.navigateToSearch(this.searchQuery);
    this.searchQuery = '';
  }
}
