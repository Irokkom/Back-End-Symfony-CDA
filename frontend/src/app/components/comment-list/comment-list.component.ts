import { Component, OnInit } from '@angular/core';
import { CommentService } from '../../services/comment.service';
import { Comment } from '../../models/comment.model';

@Component({
  selector: 'app-comment-list',
  templateUrl: './comment-list.component.html',
  styleUrls: ['./comment-list.component.scss']
})
export class CommentListComponent implements OnInit {
  comments: Comment[] = [];
  loading = false;
  error = '';

  constructor(private commentService: CommentService) {}

  ngOnInit(): void {
    this.loadComments();
  }

  loadComments(): void {
    this.loading = true;
    this.commentService.getPendingComments().subscribe({
      next: (comments) => {
        this.comments = comments;
        this.loading = false;
      },
      error: (error) => {
        this.error = 'Erreur lors du chargement des commentaires';
        this.loading = false;
      }
    });
  }

  moderateComment(id: number, status: 'approved' | 'rejected'): void {
    this.commentService.moderateComment(id, status).subscribe({
      next: () => {
        this.comments = this.comments.filter(comment => comment.id !== id);
      },
      error: (error) => {
        this.error = 'Erreur lors de la modération du commentaire';
      }
    });
  }
}
