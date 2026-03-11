<x-app-layout>
    <div class="chat-wrapper">
        <div class="chat-page">

            <aside class="sidebar">
                <div class="sidebar-header">
                    <span class="sidebar-title">Chats</span>
                </div>
                <div class="user-list">
                    @foreach($users as $user)
                        <div class="user-item {{ isset($firstUser) && $firstUser->id === $user->id ? 'active' : '' }}"
                             data-user-id="{{ $user->id }}"
                             data-user-name="{{ $user->name }}">
                            <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                            <div class="user-info">
                                <span class="user-name">{{ $user->name }}</span>
                            </div>
                            @if($user->unread_count > 0)
                                <span class="unread-badge" id="badge-{{ $user->id }}">{{ $user->unread_count }}</span>
                            @else
                                <span class="unread-badge hidden" id="badge-{{ $user->id }}"></span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </aside>

            <div class="chat-area">
                <div class="chat-header" id="chat-header">
                    <span id="chat-with">{{ isset($firstUser) ? $firstUser->name : 'Select a chat' }}</span>
                </div>

                <div class="messages-container" id="messages">
                    @foreach($messages as $message)
                        <div class="message {{ $message->sender_id === auth()->id() ? 'outgoing' : 'incoming' }}"
                             data-id="{{ $message->id }}"
                             data-read="{{ $message->read_at ? '1' : '0' }}">
                            <div class="bubble">
                                {{ $message->message }}
                                <div class="meta">
                                    <span class="time">{{ $message->created_at->format('H:i') }}</span>
                                    @if($message->sender_id === auth()->id())
                                        <span class="checks {{ $message->read_at ? 'read' : '' }}">
                                            <svg width="16" height="11" viewBox="0 0 16 11" fill="none">
                                                <path d="M1 5.5L4.5 9L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path class="second-check" d="M5 5.5L8.5 9L15 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="input-area">
                    <form class="send-form" id="main-form">
                        @csrf
                        <input type="hidden" id="receiver_id" value="{{ $firstUser?->id }}">
                        <input
                            type="text"
                            id="message-input"
                            class="message-input"
                            placeholder="Type a message..."
                            autocomplete="off"
                        >
                        <button type="submit" class="send-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>