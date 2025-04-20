import { Genre } from './genre.model';
import { Writer } from './writer.model';
export interface Book {
  id_book: number;
  title: string;
  author_string?: string;
  description?: string | null;
  cover_image_url?: string | null;
  genres?: Genre[];
  writers?: Writer[];
  average_rating?: number | null;
  review_count?: number;
  id_genre?: number;
  genre_ids?: number[];
  writer_ids?: number[];
}
