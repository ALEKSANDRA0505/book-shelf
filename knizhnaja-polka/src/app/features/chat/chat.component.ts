import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ChatComponent as NestedChatComponent } from './chat/chat.component';
@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [CommonModule, NestedChatComponent],
  template: '<app-chat></app-chat>'
})
export class ChatComponent {} 