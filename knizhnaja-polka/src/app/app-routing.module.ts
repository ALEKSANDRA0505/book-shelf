import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './features/home/home/home.component';
import { ChatComponent } from './features/chat/chat/chat.component';
import { AuthorsListComponent } from './features/authors/authors-list/authors-list.component';
import { WritersListComponent } from './features/writers/writers-list/writers-list.component';
import { AboutComponent } from './features/about/about/about.component';
import { SignInComponent } from './features/auth/sign-in/sign-in.component';
import { ReviewDetailComponent } from './features/reviews/review-detail/review-detail.component';
const routes: Routes = [
  { path: '', component: HomeComponent },
  { 
    path: 'chat',
    component: ChatComponent
  },
  { 
    path: 'all-books',
    loadChildren: () => import('./features/books/books.module').then(m => m.BooksModule)
  },
  { 
    path: 'authors-list',
    component: AuthorsListComponent
  },
  { 
    path: 'writers-list',
    component: WritersListComponent
  },
  { 
    path: 'profile',
    loadChildren: () => import('./features/profile/profile.module').then(m => m.ProfileModule)
  },
  { 
    path: 'about',
    component: AboutComponent
  },
  {
    path: 'sign-in',
    component: SignInComponent
  },
  {
    path: 'review-detail',
    component: ReviewDetailComponent
  },
  {
    path: 'admin',
    loadChildren: () => import('./features/admin/admin.module').then(m => m.AdminModule),
  },
  { path: '**', redirectTo: '' }
];
@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { } 