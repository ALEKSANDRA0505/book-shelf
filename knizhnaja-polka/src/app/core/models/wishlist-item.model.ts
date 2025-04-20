
export interface WishlistItem {
  id_wishlist_item: number;
  id_user: number;
  id_book: number;
  added_at: string;
  book_title: string;
  book_description: string | null;
  book_cover_image_url: string | null;
} 