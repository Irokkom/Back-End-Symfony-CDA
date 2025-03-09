import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { CommentListComponent } from './components/comment-list/comment-list.component';

const routes: Routes = [
  { path: '', redirectTo: '/comments', pathMatch: 'full' },
  { path: 'comments', component: CommentListComponent }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
