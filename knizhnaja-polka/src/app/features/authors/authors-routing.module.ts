import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthorsListComponent } from './authors-list/authors-list.component';
const routes: Routes = [
  { 
    path: '', 
    component: AuthorsListComponent 
  },
  { 
    path: ':id', 
    loadComponent: () => import('./author-profile/author-profile.component').then(m => m.AuthorProfileComponent)
  }
];
@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AuthorsRoutingModule { }
