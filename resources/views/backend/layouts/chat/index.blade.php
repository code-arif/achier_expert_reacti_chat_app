@extends('backend.app')

@section('title', 'Chat')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="container-fluid p-3">
                <div class="main-container container-fluid">
                    <div class="chat-container">
                        <!-- Sidebar -->
                        <div class="chat-sidebar">
                            <div class="sidebar-header">
                                <h3><i class="bi bi-chat-dots"></i> Messages</h3>
                                <div class="search-container">
                                    <input name="keyword" type="text" id="keyword" class="search-input"
                                        placeholder="Search conversations...">
                                    <div class="search-actions">
                                        <button type="button" class="search-btn" onclick="userSearch();">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <button type="button" class="refresh-btn" onclick="userList();">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="user-list" id="userList">
                                <!-- Users will be populated here -->
                            </div>
                        </div>

                        <!-- Main Chat Area -->
                        <div class="main-chat">
                            <!-- Welcome Screen -->
                            <div class="welcome-screen" id="welcomeScreen">
                                <div class="welcome-icon">
                                    <i class="bi bi-chat-heart"></i>
                                </div>
                                <div class="welcome-text">Welcome to Professional Chat</div>
                                <div class="welcome-subtext">Select a conversation to start messaging</div>
                            </div>

                            <!-- Chat Box -->
                            <div class="main-content-body main-content-body-chat d-none" id="ChatBox">
                                <!-- Chat Header -->
                                <div class="chat-header">
                                    <div class="chat-header-avatar" id="ReceiverImage">
                                        <img src="default.jpg" alt="User">
                                    </div>
                                    <div class="chat-header-info">
                                        <h3 id="ReceiverName">User</h3>
                                        <p id="ReceiverStatus">offline</p>
                                    </div>
                                    <div class="chat-actions">
                                        <button class="action-btn" onclick="formClear()" title="Refresh">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>

                                        <div class="tooltip-container">
                                            <button class="action-btn" id="deleteBtn">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <span class="tooltip-text" id="deleteTooltip"
                                                onclick="confirmDeleteConversation($('#ReceiverId').val());">
                                                <i class="bi bi-trash"></i> Delete Conversation
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chat Messages -->
                                <div class="chat-messages" id="ChatContent">
                                    <!-- Messages will be populated here -->
                                </div>

                                <!-- Chat Input -->
                                <div class="chat-input">
                                    <div class="input-container">
                                        <input class="message-input" placeholder="Type your message here..." type="text"
                                            id="Text">
                                        <label for="File" id="FileLabel" class="file-input-label">
                                            <i class="bi bi-paperclip"></i>
                                        </label>
                                        <input type="file" id="File" style="display: none;"
                                            accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt">
                                        <input type="hidden" id="ReceiverId" />
                                        <input type="hidden" id="RoomId" />
                                        <input type="hidden" id="ReplyToId" />
                                    </div>
                                    <button type="button" class="send-btn" onclick="sendMessage($('#ReceiverId').val())">
                                        <i class="bi bi-send-fill"></i>
                                    </button>
                                    <button type="button" class="clear-btn" onclick="formClear()">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        // User List
        function userList() {
            $.ajax({
                url: `{{ route('admin.chat.list') }}`,
                type: "GET",
                success: function(response) {
                    $('#userList').empty();

                    if (response.data.chats.length === 0) {
                        $('#userList').append(`
                            <div class="text-center p-4">
                                <p class="text-muted">No conversations yet</p>
                            </div>
                        `);
                        return;
                    }

                    $.each(response.data.chats, function(index, chat) {
                        let onlineStatus = chat.user.is_online ? 'online' : 'offline';
                        let unreadBadge = chat.unread_count > 0 ?
                            `<span class="unread-badge">${chat.unread_count}</span>` : '';
                        let lastMessage = chat.last_message ? chat.last_message.text :
                            'No messages yet';
                        let lastTime = chat.last_message ? chat.last_message.time : '';

                        $('#userList').append(`
                            <a class="user-item" href="javascript:void(0)" onclick="userChat(${chat.user.id})" id="selectUser${chat.user.id}">
                                <div class="user-avatar">
                                    <img alt="avatar" src="${chat.user.avatar}">
                                    <div class="online-indicator ${onlineStatus}"></div>
                                </div>
                                <div class="user-info">
                                    <div class="user-name">${chat.user.name}${unreadBadge}</div>
                                    <div class="user-message">${lastMessage}</div>
                                </div>
                                <div class="user-time">${lastTime}</div>
                            </a>
                        `);
                    });
                },
                error: function(xhr) {
                    console.error('Error loading users:', xhr);
                    Swal.fire('Error', 'Failed to load conversations', 'error');
                }
            });
        }

        // User Search
        function userSearch() {
            let keyword = $('#keyword').val();

            if (!keyword.trim()) {
                userList();
                return;
            }

            $.ajax({
                url: `{{ route('admin.chat.search') }}?keyword=${keyword}`,
                type: "GET",
                success: function(response) {
                    $('#userList').empty();

                    if (response.data.users.length === 0) {
                        $('#userList').append(`
                            <div class="text-center p-4">
                                <p class="text-muted">No users found</p>
                            </div>
                        `);
                        return;
                    }

                    $.each(response.data.users, function(index, user) {
                        let onlineStatus = user.is_online ? 'online' : 'offline';

                        $('#userList').append(`
                            <a class="user-item" href="javascript:void(0)" onclick="userChat(${user.id})" id="selectUser${user.id}">
                                <div class="user-avatar">
                                    <img alt="avatar" src="${user.avatar}">
                                    <div class="online-indicator ${onlineStatus}"></div>
                                </div>
                                <div class="user-info">
                                    <div class="user-name">${user.name}</div>
                                    <div class="user-message">${user.email}</div>
                                </div>
                            </a>
                        `);
                    });
                },
                error: function(xhr) {
                    console.error('Error searching users:', xhr);
                }
            });
        }
    </script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>

    <script>
        // Initialize Laravel Echo with Reverb
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('broadcasting.connections.reverb.key') }}',
            wsHost: '{{ config('broadcasting.connections.reverb.host') }}',
            wsPort: {{ config('broadcasting.connections.reverb.port') }},
            wssPort: {{ config('broadcasting.connections.reverb.port') }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });

        let typingTimeout;
        const authUserId = {{ auth()->id() }};


        // Load Conversation
        function userChat(receiverId) {
            $.ajax({
                url: `{{ route('admin.chat.conversation', ':id') }}`.replace(':id', receiverId),
                type: "GET",
                success: function(response) {
                    $('#ChatContent').empty();
                    $('#ReceiverId').val(receiverId);
                    $('#ReceiverName').text(response.data.receiver.name);
                    $('#RoomId').val(response.data.room_id);

                    // Update online status
                    let statusText = response.data.receiver.is_online ?
                        '<span class="text-success">● Online</span>' :
                        `Last seen ${response.data.receiver.last_activity}`;
                    $('#ReceiverStatus').html(statusText);

                    // Update avatar
                    $('#ReceiverImage').html(`<img alt="avatar" src="${response.data.receiver.avatar}">`);

                    // Show chat box
                    $('#welcomeScreen').hide();
                    $('#ChatBox').removeClass('d-none');

                    // Update selected user
                    $('.user-item').removeClass('selected');
                    $('#selectUser' + receiverId).addClass('selected');

                    // Display messages
                    displayMessages(response.data.messages);

                    // Mark messages as read
                    markMessagesAsRead(response.data.room_id);

                    // Scroll to bottom
                    scrollToBottom();

                    // Listen to room channel
                    listenToRoom(response.data.room_id);
                },
                error: function(xhr) {
                    console.error('Error loading conversation:', xhr);
                    Swal.fire('Error', 'Failed to load conversation', 'error');
                }
            });
        }

        // Display Messages
        function displayMessages(messages) {
            messages.forEach(message => {
                appendMessage(message);
            });
        }

        // Append Message
        function appendMessage(message) {
            let chatClass = message.is_own ? 'message chat-right' : 'message chat-left';
            let messageContent = '';

            // Reply to message
            if (message.reply_to) {
                messageContent += `
                    <div class="reply-message">
                        <div class="reply-sender">${message.reply_to.sender_name}</div>
                        <div>${message.reply_to.message}</div>
                    </div>
                `;
            }

            // Message based on type
            if (message.type === 'text') {
                messageContent += `<div class="message-bubble">${message.message}</div>`;
            } else if (message.type === 'image') {
                messageContent += `
                    <div class="message-bubble">
                        <a href="${message.file}" target="_blank">
                            <img src="${message.thumbnail || message.file}" class="message-image" alt="Image">
                        </a>
                    </div>
                `;
            } else if (message.type === 'video') {
                messageContent += `
                    <div class="message-bubble">
                        <video controls style="max-width: 300px; border-radius: 12px;">
                            <source src="${message.file}" type="video/mp4">
                        </video>
                    </div>
                `;
            } else if (message.type === 'audio') {
                messageContent += `
                    <div class="message-bubble">
                        <audio controls style="width: 250px;">
                            <source src="${message.file}" type="audio/mpeg">
                        </audio>
                    </div>
                `;
            } else if (message.type === 'document') {
                messageContent += `
                    <div class="message-bubble">
                        <div class="message-file">
                            <i class="bi bi-file-earmark-text file-icon"></i>
                            <div class="file-info">
                                <div class="file-name">${message.file_name}</div>
                                <div class="file-size">${formatFileSize(message.file_size)}</div>
                            </div>
                            <a href="${message.file}" download class="btn btn-sm btn-primary">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                `;
            }

            // Status indicator
            let statusIcon = '';
            if (message.is_own) {
                if (message.status === 'read') {
                    statusIcon = '<i class="bi bi-check-all text-primary"></i>';
                } else if (message.status === 'delivered') {
                    statusIcon = '<i class="bi bi-check-all"></i>';
                } else {
                    statusIcon = '<i class="bi bi-check"></i>';
                }
            }

            $('#ChatContent').append(`
                <div class="${chatClass}" data-message-id="${message.id}">
                    <div class="message-avatar">
                        <img alt="avatar" src="${message.sender.avatar}">
                    </div>
                    <div class="message-content">
                        ${messageContent}
                        <div class="message-time">
                            ${message.created_at}
                            <span class="message-status">${statusIcon}</span>
                        </div>
                    </div>
                </div>
            `);
        }

        // Send Message
        function sendMessage(receiverId) {
            let text = $('#Text').val();
            let file = $('#File')[0].files[0];

            if (!text && !file) {
                Swal.fire('Warning', 'Please enter a message or select a file', 'warning');
                return;
            }

            let formData = new FormData();

            if (text) {
                formData.append('message', text);
            }

            if (file) {
                formData.append('file', file);
                // Determine file type
                let fileType = 'document';
                if (file.type.startsWith('image/')) {
                    fileType = 'image';
                } else if (file.type.startsWith('video/')) {
                    fileType = 'video';
                } else if (file.type.startsWith('audio/')) {
                    fileType = 'audio';
                }
                formData.append('type', fileType);
            } else {
                formData.append('type', 'text');
            }

            let replyToId = $('#ReplyToId').val();
            if (replyToId) {
                formData.append('reply_to_id', replyToId);
            }

            $.ajax({
                url: `{{ route('admin.chat.send', ':id') }}`.replace(':id', receiverId),
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    formClear();
                    appendMessage(response.data.chat);
                    scrollToBottom();
                    userList();
                },
                error: function(xhr) {
                    console.error('Error sending message:', xhr);
                    Swal.fire('Error', 'Failed to send message', 'error');
                }
            });
        }

        // Mark messages as read
        function markMessagesAsRead(roomId) {
            $.ajax({
                url: `{{ route('admin.chat.mark.read', ':id') }}`.replace(':id', roomId),
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function() {
                    userList();
                }
            });
        }

        // File input change
        $('#File').on('change', function() {
            let file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        $('#FileLabel').html(
                            `<img src="${e.target.result}" style="width: 20px; height: 20px; border-radius: 3px;"/>`
                        );
                    } else {
                        $('#FileLabel').html(`<i class="bi bi-check-circle-fill"></i>`);
                    }
                    $('#FileLabel').addClass('has-file');
                };
                reader.readAsDataURL(file);
            }
        });

        // Clear form
        function formClear() {
            $('#FileLabel').html(`<i class="bi bi-paperclip"></i>`);
            $('#FileLabel').removeClass('has-file');
            $('#File').val('');
            $('#Text').val('');
            $('#ReplyToId').val('');
        }

        // Enter key to send
        $('#Text').on('keypress', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                let receiverId = $('#ReceiverId').val();
                if (receiverId) {
                    sendMessage(receiverId);
                }
            }
        });

        // Typing indicator
        $('#Text').on('input', function() {
            let roomId = $('#RoomId').val();
            if (roomId) {
                clearTimeout(typingTimeout);
                sendTypingIndicator(roomId, true);

                typingTimeout = setTimeout(() => {
                    sendTypingIndicator(roomId, false);
                }, 1000);
            }
        });

        function sendTypingIndicator(roomId, isTyping) {
            $.ajax({
                url: `{{ route('admin.chat.typing', ':id') }}`.replace(':id', roomId),
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: {
                    is_typing: isTyping
                }
            });
        }

        // Search on Enter
        $('#keyword').on('keypress', function(e) {
            if (e.which === 13) {
                userSearch();
            }
        });

        // Delete conversation
        function confirmDeleteConversation(receiverId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the entire conversation!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('admin.chat.conversation.delete', ':id') }}`.replace(':id',
                            receiverId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success');
                                $('#welcomeScreen').show();
                                $('#ChatBox').addClass('d-none');
                                userList();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Failed to delete conversation', 'error');
                        }
                    });
                }
            });
        }

        // Tooltip toggle
        document.getElementById('deleteBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('deleteTooltip').classList.toggle('show');
        });

        document.addEventListener('click', function() {
            document.getElementById('deleteTooltip').classList.remove('show');
        });

        // Scroll to bottom
        function scrollToBottom() {
            $('#ChatContent').scrollTop($('#ChatContent')[0].scrollHeight);
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Laravel Echo Listeners
        function listenToRoom(roomId) {
            // Listen for new messages
            Echo.private(`chat-room.${roomId}`)
                .listen('MessageSendEvent', (e) => {
                    if (e.chat.sender.id !== authUserId) {
                        appendMessage(e.chat);
                        scrollToBottom();
                        markMessagesAsRead(roomId);

                        // Show notification
                        if (Notification.permission === "granted") {
                            new Notification(e.chat.sender.name, {
                                body: e.chat.message || 'New message',
                                icon: e.chat.sender.avatar
                            });
                        }
                    }
                })
                .listen('TypingEvent', (e) => {
                    if (e.user_id !== authUserId) {
                        if (e.is_typing) {
                            $('#ReceiverStatus').html('<span class="typing-indicator-text">typing...</span>');
                        } else {
                            let receiverId = $('#ReceiverId').val();
                            // Restore online status
                            userChat(receiverId);
                        }
                    }
                })
                .listen('MessageReadEvent', (e) => {
                    // Update message status to read
                    $('.message.chat-right .message-status').html('<i class="bi bi-check-all text-primary"></i>');
                })
                .listen('MessageDeletedEvent', (e) => {
                    // Remove or update deleted message
                    if (e.delete_type === 'for_everyone') {
                        $(`.message[data-message-id="${e.chat_id}"]`).find('.message-bubble').html(
                            '<em>This message was deleted</em>');
                    }
                });
        }

        // Listen to personal channel for notifications
        Echo.private(`chat-receiver.${authUserId}`)
            .listen('MessageSendEvent', (e) => {
                userList(); // Refresh user list
            });


        // Listen to user status
        Echo.channel('user-status')
            .listen('UserOnlineEvent', (e) => {
                // Update online status in user list
                let indicator = $(`#selectUser${e.user_id} .online-indicator`);
                if (e.is_online) {
                    indicator.removeClass('offline').addClass('online');
                } else {
                    indicator.removeClass('online').addClass('offline');
                }
            });

        // Request notification permission
        if (Notification.permission === "default") {
            Notification.requestPermission();
        }

        // Initialize
        userList();

        // Auto-refresh user list every 5 minutes
        setInterval(() => {
            userList();
        }, 300000);
    </script>
@endpush


@push('styles')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            height: calc(100vh - 140px);
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .chat-sidebar {
            width: 380px;
            border-right: 1px solid #e4e6eb;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e4e6eb;
        }

        .sidebar-header h3 {
            font-size: 24px;
            font-weight: 700;
            color: #050505;
            margin-bottom: 15px;
        }

        .search-container {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 1px solid #e4e6eb;
            border-radius: 20px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #0084ff;
            box-shadow: 0 0 0 3px rgba(0, 132, 255, 0.1);
        }

        .search-actions {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            gap: 5px;
        }

        .search-btn,
        .refresh-btn {
            padding: 5px 10px;
            border: none;
            background: #0084ff;
            color: white;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .refresh-btn {
            background: #42b72a;
        }

        .search-btn:hover {
            background: #0073e6;
        }

        .refresh-btn:hover {
            background: #36a420;
        }

        /* User List */
        .user-list {
            flex: 1;
            overflow-y: auto;
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid #f0f2f5;
        }

        .user-item:hover {
            background: #f0f2f5;
        }

        .user-item.selected {
            background: #e7f3ff;
            border-left: 3px solid #0084ff;
        }

        .user-avatar {
            position: relative;
            margin-right: 12px;
        }

        .user-avatar img {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
        }

        .online-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid white;
        }

        .online-indicator.online {
            background: #42b72a;
        }

        .online-indicator.offline {
            background: #8a8d91;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-weight: 600;
            font-size: 15px;
            color: #050505;
            margin-bottom: 4px;
        }

        .user-message {
            font-size: 13px;
            color: #65676b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-time {
            font-size: 12px;
            color: #65676b;
            white-space: nowrap;
        }

        .unread-badge {
            background: #0084ff;
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 5px;
        }

        /* Main Chat Area */
        .main-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f0f2f5;
        }

        /* Welcome Screen */
        .welcome-screen {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: white;
        }

        .welcome-icon {
            font-size: 100px;
            color: #0084ff;
            margin-bottom: 20px;
        }

        .welcome-text {
            font-size: 28px;
            font-weight: 600;
            color: #050505;
            margin-bottom: 10px;
        }

        .welcome-subtext {
            font-size: 16px;
            color: #65676b;
        }

        /* Chat Header */
        .chat-header {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: white;
            border-bottom: 1px solid #e4e6eb;
        }

        .chat-header-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 12px;
        }

        .chat-header-info {
            flex: 1;
        }

        .chat-header-info h3 {
            font-size: 16px;
            font-weight: 600;
            color: #050505;
            margin: 0;
        }

        .chat-header-info p {
            font-size: 13px;
            color: #65676b;
            margin: 0;
        }

        .typing-indicator-text {
            color: #0084ff;
            font-style: italic;
        }

        .chat-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: #f0f2f5;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .action-btn:hover {
            background: #e4e6eb;
        }

        .tooltip-container {
            position: relative;
        }

        .tooltip-text {
            visibility: hidden;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            color: #050505;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            white-space: nowrap;
            z-index: 1000;
            margin-top: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .tooltip-text:hover {
            background: #f0f2f5;
        }

        .tooltip-text.show {
            visibility: visible;
        }

        /* Chat Messages */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f0f2f5;
        }

        .message {
            display: flex;
            margin-bottom: 15px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-avatar img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .message-content {
            max-width: 60%;
        }

        .message-bubble {
            padding: 10px 14px;
            border-radius: 18px;
            margin-bottom: 4px;
            word-wrap: break-word;
        }

        .chat-left .message-bubble {
            background: white;
            color: #050505;
        }

        .chat-right {
            flex-direction: row-reverse;
        }

        .chat-right .message-avatar {
            margin-right: 0;
            margin-left: 8px;
        }

        .chat-right .message-content {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .chat-right .message-bubble {
            background: #0084ff;
            color: white;
        }

        .message-image {
            max-width: 300px;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .message-image:hover {
            transform: scale(1.02);
        }

        .message-file {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 12px;
        }

        .file-icon {
            font-size: 32px;
        }

        .file-info {
            flex: 1;
        }

        .file-name {
            font-weight: 600;
            font-size: 14px;
        }

        .file-size {
            font-size: 12px;
            opacity: 0.7;
        }

        .message-time {
            font-size: 11px;
            color: #65676b;
            margin-top: 4px;
        }

        .chat-right .message-time {
            color: rgba(255, 255, 255, 0.7);
        }

        .message-status {
            display: inline-block;
            margin-left: 4px;
        }

        /* Chat Input */
        .chat-input {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
            background: white;
            border-top: 1px solid #e4e6eb;
        }

        .input-container {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f0f2f5;
            border-radius: 20px;
            padding: 0 15px;
        }

        .message-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 10px 0;
            font-size: 15px;
            outline: none;
        }

        .file-input-label {
            cursor: pointer;
            color: #0084ff;
            font-size: 20px;
            transition: transform 0.2s;
        }

        .file-input-label:hover {
            transform: scale(1.1);
        }

        .file-input-label.has-file {
            color: #42b72a;
        }

        .send-btn,
        .clear-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .send-btn {
            background: #0084ff;
            color: white;
        }

        .send-btn:hover {
            background: #0073e6;
            transform: scale(1.05);
        }

        .clear-btn {
            background: #f0f2f5;
            color: #65676b;
        }

        .clear-btn:hover {
            background: #e4e6eb;
        }

        /* Scrollbar Styling */
        .user-list::-webkit-scrollbar,
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .user-list::-webkit-scrollbar-track,
        .chat-messages::-webkit-scrollbar-track {
            background: #f0f2f5;
        }

        .user-list::-webkit-scrollbar-thumb,
        .chat-messages::-webkit-scrollbar-thumb {
            background: #bcc0c4;
            border-radius: 4px;
        }

        .user-list::-webkit-scrollbar-thumb:hover,
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #8a8d91;
        }

        /* Reply Message Style */
        .reply-message {
            background: rgba(0, 0, 0, 0.05);
            border-left: 3px solid #0084ff;
            padding: 8px;
            margin-bottom: 8px;
            border-radius: 8px;
            font-size: 13px;
        }

        .reply-sender {
            font-weight: 600;
            color: #0084ff;
            margin-bottom: 4px;
        }

        /* Loading Animation */
        .loading {
            text-align: center;
            padding: 20px;
            color: #65676b;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #0084ff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .chat-sidebar {
                width: 100%;
            }

            .main-chat {
                position: absolute;
                left: 0;
                right: 0;
                top: 0;
                bottom: 0;
                z-index: 10;
            }

            .message-content {
                max-width: 80%;
            }
        }
    </style>
@endpush
