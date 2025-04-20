
export interface ChatMessage {
  id_message: number;
  id_user: number;
  user_username: string;
  user_profile_picture_url: string | null;
  message_text: string;
  sent_at: string;
}
