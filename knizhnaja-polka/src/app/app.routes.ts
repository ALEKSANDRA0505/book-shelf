import { Routes } from '@angular/router';
import { HomeComponent } from './features/home/home/home.component';
import { authGuard } from './core/guards/auth.guard';
import { loginGuard } from './core/guards/login.guard';
export const routes: Routes = [
  { path: '', component: HomeComponent },
  { 
    path: 'all-books',
    loadComponent: () => import('./features/books/all-books/all-books.component').then(m => m.AllBooksComponent)
  },
  { 
    path: 'chat',
    loadComponent: () => import('./features/chat/chat/chat.component').then(m => m.ChatComponent),
    canActivate: [authGuard]
  },
  { 
    path: 'book-detail',
    loadComponent: () => import('./features/books/book-detail/book-detail.component').then(m => m.BookDetailComponent)
  },
  { 
    path: 'book-shelf',
    loadComponent: () => import('./features/books/book-shelf/book-shelf.component').then(m => m.PolkaComponent)
  },
  { 
    path: 'genres/:slug',
    loadComponent: () => import('./features/books/genres/genres.component').then(m => m.GenresComponent)
  },
  { 
    path: 'writer-profile/:id',
    loadComponent: () => import('./features/writers/writer-profile/writer-profile.component').then(m => m.WriterProfileComponent)
  },
  { 
    path: 'author-profile',
    loadComponent: () => import('./features/authors/author-profile/author-profile.component').then(m => m.AuthorProfileComponent)
  },
  { 
    path: 'authors-list',
    loadComponent: () => import('./features/authors/authors-list/authors-list.component').then(m => m.AuthorsListComponent)
  },
  { 
    path: 'writers-list',
    loadComponent: () => import('./features/writers/writers-list/writers-list.component').then(m => m.WritersListComponent)
  },
  { 
    path: 'user-profile',
    loadComponent: () => import('./features/profile/user-profile/user-profile.component').then(m => m.UserProfileComponent),
    canActivate: [authGuard]
  },
  {
    path: 'about',
    loadComponent: () => import('./features/about/about/about.component').then(m => m.AboutComponent)
  },
  {
    path: 'search',
    loadComponent: () => import('./features/search/search.component').then(m => m.SearchComponent)
  },
  {
    path: 'auth/sign-in',
    loadComponent: () => import('./features/auth/sign-in/sign-in.component').then(m => m.SignInComponent),
    canActivate: [loginGuard]
  },
  {
    path: 'review-detail/:id',
    loadComponent: () => import('./features/reviews/review-detail/review-detail.component').then(m => m.ReviewDetailComponent)
  },
  {
    path: 'review-list',
    loadComponent: () => import('./features/reviews/review-list/review-list.component').then(m => m.ReviewListComponent)
  },
  {
    path: 'admin',
    loadChildren: () => import('./features/admin/admin.module').then(m => m.AdminModule),
    canActivate: [authGuard]
  },
  { path: '**', redirectTo: '' }
];