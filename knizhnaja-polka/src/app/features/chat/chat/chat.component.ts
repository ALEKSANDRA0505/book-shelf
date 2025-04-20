import { Component, OnInit, ViewChild, ElementRef, AfterViewChecked, ChangeDetectorRef, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule, DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { ChatMessage } from '../../../core/models/message.model';
import { ChatMessageService } from '../../../core/services/chat-message.service';
import { AuthService } from '../../../core/services/auth.service';
import { User } from '../../../core/models/user.model';
import { Subscription } from 'rxjs';
import { ChatbotService, ChatbotResponse } from '../../../core/services/chatbot.service';
interface Book {
  title: string;
  author: string;
  desc: string;
}
interface BookDatabase {
  [genre: string]: Book[];
}
interface Message {
  type: 'sent' | 'received';
  content: string;
  author?: string;
  avatar?: string;
  time: string;
  profileLink?: string;
  isTyping?: boolean;
  text?: string;
  avatarUrl?: string;
  authorLink?: string;
}
interface BotMessage {
  type: 'sent' | 'received';
  text: string;
  time: string;
  isTyping?: boolean;
}
interface DisplayMessage extends ChatMessage {
  type: 'sent' | 'received';
}
@Component({
  selector: 'app-chat',
  templateUrl: './chat.component.html',
  styleUrls: ['./chat.component.css'],
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule, DatePipe],

})
export class ChatComponent implements OnInit, AfterViewChecked, OnDestroy {
  @ViewChild('botMessagesContainer') private botMessagesContainer!: ElementRef;
  @ViewChild('publicMessagesContainer') private publicMessagesContainer!: ElementRef;
  @ViewChild('messageInput') private messageInput!: ElementRef;
  currentMode: 'bot' | 'public' = 'bot';
  chatTitle: string = 'Книжный помощник';
  messageText: string = '';
  publicMessages: DisplayMessage[] = [];
  botMessages: DisplayMessage[] = [];
  suggestions: string[] = [
    'Люблю фэнтези с глубоким сюжетом',
    'Ищу детективы, похожие на Агату Кристи',
    'Подборка классики для начинающих',
    'Современная литература о психологии',
    'Научная фантастика с философским подтекстом'
  ];
  isTyping: boolean = false;
  bookDatabase: BookDatabase = {
    "фэнтези": [
      { title: "Имя ветра", author: "Патрик Ротфусс", desc: "Эпическое фэнтези с глубокой системой магии" },
      { title: "Игра престолов", author: "Джордж Р.Р. Мартин", desc: "Политические интриги в фэнтезийном мире" },
      { title: "Ведьмак", author: "Анджей Сапковский", desc: "Тёмное фэнтези с славянскими мотивами" }
    ],
    "детектив": [
      { title: "Убийство в «Восточном экспрессе»", author: "Агата Кристи", desc: "Классический детектив от королевы жанра" },
      { title: "Тёмные тайны", author: "Гиллиан Флинн", desc: "Современный психологический триллер" },
      { title: "Шерлок Холмс", author: "Артур Конан Дойл", desc: "Легендарные истории о гениальном сыщике" }
    ],
    "классика": [
      { title: "Преступление и наказание", author: "Фёдор Достоевский", desc: "Классический роман о морали и искуплении" },
      { title: "Мастер и Маргарита", author: "Михаил Булгаков", desc: "Мистический роман о визите дьявола в Москву" },
      { title: "Гордость и предубеждение", author: "Джейн Остин", desc: "Роман о любви и социальных условностях" }
    ],
    "научная фантастика": [
      { title: "Дюна", author: "Фрэнк Герберт", desc: "Эпическая сага о политике и религии на далёкой планете" },
      { title: "Солярис", author: "Станислав Лем", desc: "Философское исследование контакта с инопланетным разумом" },
      { title: "Нейромант", author: "Уильям Гибсон", desc: "Киберпанк о хакере и искусственном интеллекте" }
    ],
    "роман": [
      { title: "Сто лет одиночества", author: "Габриэль Гарсиа Маркес", desc: "Магический реализм в истории семьи Буэндиа" },
      { title: "1984", author: "Джордж Оруэлл", desc: "Антиутопия о тоталитарном обществе" },
      { title: "Убить пересмешника", author: "Харпер Ли", desc: "Роман о расовой несправедливости в американском обществе" }
    ]
  };
  botCommands = [
    { text: 'Помощь',             display_text: 'Помощь',                      icon: 'fas fa-question-circle' },
    { text: 'Найди название',     display_text: 'Найди название <название>',   icon: 'fas fa-search' },
    { text: 'Найди писателя',     display_text: 'Найди писателя <автор>',     icon: 'fas fa-user-edit' },
    { text: 'Инфо книга',         display_text: 'Инфо книга <название>',       icon: 'fas fa-info-circle' },
    { text: 'Посоветуй книгу',    display_text: 'Посоветуй книгу',             icon: 'fas fa-random' },
    { text: 'Посоветуй по жанру', display_text: 'Посоветуй по жанру <жанр>',   icon: 'fas fa-tags' }
  ];
  
  chatRules = [
    { icon: 'fas fa-heart', text: 'Будьте вежливы с другими участниками' },
    { icon: 'fas fa-ban', text: 'Не используйте ненормативную лексику' },
    { icon: 'fas fa-book', text: 'Старайтесь придерживаться темы книг и чтения' },
    { icon: 'fas fa-user-shield', text: 'Не делитесь личной информацией' }
  ];
  isLoadingPublicMessages: boolean = false;
  publicMessagesError: string | null = null;
  isSendingMessage: boolean = false;
  sendMessageError: string | null = null;
  isBotTyping: boolean = false;
  botError: string | null = null;
  private authSubscription: Subscription | null = null;
  private messagesSubscription: Subscription | null = null;
  private sendMessageSubscription: Subscription | null = null;
  private chatbotSubscription: Subscription | null = null;
  currentUserId: number | null = null;
  currentUserProfileImageUrl: string | null = null;
  currentUserUsername: string | null = null;
  private shouldScrollBot = false;
  private shouldScrollPublic = false;
  constructor(
    private router: Router,
    private cdRef: ChangeDetectorRef,
    private chatMessageService: ChatMessageService,
    private authService: AuthService,
    private chatbotService: ChatbotService
  ) { }
  ngOnInit(): void {
    this.authSubscription = this.authService.currentUser$.subscribe(user => {
      this.currentUserId = user ? user.id_user : null;
      this.currentUserProfileImageUrl = user?.profile_picture_url ?? null;
      this.currentUserUsername = user?.username ?? null;
      console.log('Current user ID:', this.currentUserId);
    });
    this.loadInitialMessages();
  }
  ngOnDestroy(): void {
    this.authSubscription?.unsubscribe();
    this.messagesSubscription?.unsubscribe();
    this.sendMessageSubscription?.unsubscribe();
    this.chatbotSubscription?.unsubscribe();
  }
  ngAfterViewChecked() {
    if (this.shouldScrollBot) {
      this.scrollToBottom(this.botMessagesContainer);
      this.shouldScrollBot = false;
      this.cdRef.detectChanges();
    }
    if (this.shouldScrollPublic) {
      this.scrollToBottom(this.publicMessagesContainer);
      this.shouldScrollPublic = false;
      this.cdRef.detectChanges();
    }
  }
  loadInitialMessages() {
    console.log('Загрузка начальных сообщений...');
    this.botMessages = [
      {
        id_message: -1,
        id_user: 0,
        user_username: 'Книжный помощник',
        message_text: 'Привет! Я книжный бот. Введите \'помощь\' для списка команд.',
        sent_at: new Date().toISOString(),
        user_profile_picture_url: './assets/images/bot-avatar.png',
        type: 'received'
      }
    ];
    this.publicMessages = [];
    this.shouldScrollBot = true;
    this.loadPublicMessages();
  }
  loadPublicMessages(): void {
    console.log('Загрузка сообщений общего чата...');
    this.isLoadingPublicMessages = true;
    this.publicMessagesError = null;
    this.messagesSubscription?.unsubscribe();
    this.messagesSubscription = this.chatMessageService.getMessages(50)
      .subscribe({
        next: (messages) => {
          this.publicMessages = messages.map(msg => ({
            ...msg,
            type: msg.id_user === this.currentUserId ? 'sent' : 'received'
          }));
          this.isLoadingPublicMessages = false;
          console.log('Сообщения общего чата загружены:', this.publicMessages);
          this.shouldScrollPublic = true; 
          this.cdRef.detectChanges();
        },
        error: (error) => {
          console.error('Ошибка загрузки сообщений общего чата:', error);
          this.publicMessagesError = 'Не удалось загрузить сообщения. Попробуйте позже.';
          this.isLoadingPublicMessages = false;
        }
      });
  }
  toggleMode(mode: 'bot' | 'public'): void {
    this.currentMode = mode;
    this.chatTitle = mode === 'bot' ? 'Книжный помощник' : 'Общий чат';
    if (mode === 'bot') this.shouldScrollBot = true;
    if (mode === 'public') this.shouldScrollPublic = true;
    setTimeout(() => this.messageInput.nativeElement.focus(), 0);
  }
  getInputPlaceholder(): string {
    return this.currentMode === 'bot' 
      ? 'Введите команду (например, \'помощь\')...'
      : 'Введите сообщение...';
  }
  useSuggestion(text: string): void {
    this.messageText = text;
    this.focusInput();
  }
  setCommandText(command: string): void {
    this.messageText = command;
    this.focusInput();
  }
  private focusInput(): void {
    setTimeout(() => this.messageInput?.nativeElement.focus(), 0);
  }
  sendMessage(): void {
    const text = this.messageText.trim();
    if (!text) return;
    if (this.currentMode === 'bot') {
      this.sendBotMessage(text);
    } else {
      this.sendPublicMessage(text);
    }
    this.messageText = '';
    setTimeout(() => this.messageInput.nativeElement.focus(), 0);
  }
  sendBotMessage(text: string): void {
    const userMsg: DisplayMessage = {
      id_message: Date.now(),
      id_user: this.currentUserId ?? -2,
      user_username: this.currentUserUsername ?? 'Вы',
      message_text: text,
      sent_at: new Date().toISOString(),
      user_profile_picture_url: this.currentUserProfileImageUrl,
      type: 'sent'
    };
    this.botMessages = [...this.botMessages, userMsg];
    this.shouldScrollBot = true;
    this.cdRef.detectChanges();
    this.isBotTyping = true;
    this.botError = null;
    this.chatbotSubscription?.unsubscribe();
    this.chatbotSubscription = this.chatbotService.sendMessage(text).subscribe({
      next: (response: ChatbotResponse) => {
        this.isBotTyping = false;
        const botMsg: DisplayMessage = {
          id_message: Date.now() + 1,
          id_user: 0,
          user_username: 'Книжный помощник',
          message_text: response.message,
          sent_at: new Date().toISOString(),
          user_profile_picture_url: './assets/images/bot-avatar.png',
          type: 'received'
        };
        this.botMessages = [...this.botMessages, botMsg];
        this.shouldScrollBot = true;
        this.cdRef.detectChanges();
      },
      error: (error) => {
        console.error('Ошибка при общении с ботом:', error);
        this.isBotTyping = false;
        this.botError = 'Не удалось получить ответ от помощника. Попробуйте позже.';
        const errorMsg: DisplayMessage = {
          id_message: Date.now() + 1,
          id_user: 0,
          user_username: 'Книжный помощник',
          message_text: this.botError,
          sent_at: new Date().toISOString(),
          user_profile_picture_url: './assets/images/bot-avatar.png',
          type: 'received'
        };
        this.botMessages = [...this.botMessages, errorMsg];
        this.shouldScrollBot = true;
        this.cdRef.detectChanges();
      }
    });
  }
  sendPublicMessage(text: string): void {
    if (!this.currentUserId) {
      console.error('Пользователь не аутентифицирован для отправки сообщения');
      this.sendMessageError = 'Войдите в систему, чтобы отправлять сообщения.';
      return;
    }
    this.isSendingMessage = true;
    this.sendMessageError = null;
    this.sendMessageSubscription?.unsubscribe();
    const tempMsg: DisplayMessage = {
      id_message: Date.now(),
      id_user: this.currentUserId,
      user_username: this.currentUserUsername ?? 'Вы',
      message_text: text,
      sent_at: new Date().toISOString(),
      user_profile_picture_url: this.currentUserProfileImageUrl,
      type: 'sent'
    };
    this.publicMessages = [...this.publicMessages, tempMsg];
    this.shouldScrollPublic = true;
    this.cdRef.detectChanges();
    const messageToSend = { 
       id_user: this.currentUserId, 
       message_text: text 
     };
    this.sendMessageSubscription = this.chatMessageService.sendMessage(messageToSend).subscribe({
      next: (newMessageResponse) => {
        this.isSendingMessage = false;
        this.publicMessages = this.publicMessages.filter(msg => msg.id_message !== tempMsg.id_message);
        const displayNewMessage: DisplayMessage = {
           id_message: newMessageResponse.id_message,
           id_user: newMessageResponse.id_user,
           user_username: this.currentUserUsername ?? 'Вы',
           user_profile_picture_url: this.currentUserProfileImageUrl,
           message_text: newMessageResponse.message_text,
           sent_at: new Date().toISOString(),
           type: 'sent'
        };
        this.publicMessages = [...this.publicMessages, displayNewMessage];
        console.log('Сообщение успешно отправлено:', newMessageResponse);
        this.shouldScrollPublic = true;
        this.cdRef.detectChanges();
      },
      error: (error) => {
        console.error('Ошибка отправки сообщения:', error);
        this.isSendingMessage = false;
        this.sendMessageError = 'Не удалось отправить сообщение. Попробуйте позже.';
        this.publicMessages = this.publicMessages.filter(msg => msg.id_message !== tempMsg.id_message);
        this.cdRef.detectChanges();
      }
    });
  }
  scrollToBottom(container: ElementRef): void {
    try {
      container.nativeElement.scrollTop = container.nativeElement.scrollHeight;
    } catch(err) { console.error('Ошибка прокрутки:', err); }
  }
  goToProfile(): void {
    this.router.navigate(['/profil']);
  }
}