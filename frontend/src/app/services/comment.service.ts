import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Comment } from '../models/comment.model';

@Injectable({
  providedIn: 'root'
})
export class CommentService {
  private apiUrl = 'http://localhost:8000/api/comments';

  constructor(private http: HttpClient) {}

  getPendingComments(): Observable<Comment[]> {
    return this.http.get<Comment[]>(`${this.apiUrl}/pending`);
  }

  moderateComment(id: number, status: 'approved' | 'rejected'): Observable<any> {
    return this.http.post(`${this.apiUrl}/${id}/moderate`, { status });
  }
}
