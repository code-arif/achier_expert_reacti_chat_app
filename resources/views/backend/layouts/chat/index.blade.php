@extends('backend.app')

@section('title', 'Chat')
@push('styles')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .chat-container {
            display: flex;
            height: 85vh;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        /* Sidebar */
        .chat-sidebar {
            width: 350px;
            background: #fff;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            background: #394329;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }


        .sidebar-header h3 {
            color: white;
            font-size: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-container {
            position: relative;
            margin-bottom: 10px;
        }

        .search-input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 14px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }

        .search-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .search-btn,
        .refresh-btn {
            flex: 1;
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
        }

        .search-btn {
            background: linear-gradient(45deg, #55c7d9, #55c7d9);
        }

        .refresh-btn {
            background: linear-gradient(45deg, #95a5a6, #7f8c8d);
        }

        .search-btn:hover,
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* User List */
        .user-list {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            /* scrollbar-color: rgba(14, 7, 7, 0.966) transparent; */
            scrollbar-color: #3498db4d transparent;
            max-height: calc(100vh - 150px);
            padding-right: 5px;
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            text-decoration: none;
            color: white;
        }

        .user-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
            color: white;
            text-decoration: none;
        }

        .user-item.selected {
            background: linear-gradient(90deg, rgba(52, 152, 219, 0.3), rgba(41, 128, 185, 0.3));
            border-left: 4px solid #55c7d9;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .online-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid white;
        }

        .online-indicator.online {
            background: #27ae60;
            animation: pulse 2s infinite;
        }

        .online-indicator.offline {
            background: #e74c3c;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(39, 174, 96, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(39, 174, 96, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(39, 174, 96, 0);
            }
        }

        .user-info {
            flex: 1;
            color: rgb(26, 24, 24);
        }

        .user-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .user-message {
            font-size: 13px;
            color: rgb(26, 24, 24);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }

        .user-time {
            font-size: 12px;
            color: rgba(26, 25, 25, 0.5);
            position: absolute;
            top: 15px;
            right: 15px;
        }

        /* Main Chat Area */
        .main-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 20px 25px;
            background: linear-gradient(90deg, #f8f9fa, #e9ecef);
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-header-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #55c7d9;
        }

        .chat-header-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chat-header-info h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .chat-header-info h3:hover {
            color: #55c7d9;
        }

        .chat-header-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }

        .chat-actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: linear-gradient(45deg, #55c7d9, #55c7d9);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        /* Tooltrip */
        .tooltip-container {
            position: relative;
            display: inline-block;
        }

        .tooltip-text {
            display: none;
            /* hidden by default */
            position: absolute;
            top: 120%;
            /* show under button */
            left: 50%;
            transform: translateX(-80%);
            background: #333;
            color: #fff;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 999;
            cursor: pointer;
        }

        .tooltip-text::after {
            content: "";
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px;
            border-style: solid;
            border-color: transparent transparent #333 transparent;
        }

        .tooltip-text.show {
            display: block;
        }


        .main-content-body-chat {
            display: flex;
            flex-direction: column;
            height: 100%;
        }


        /* Chat Messages */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            scrollbar-width: thin;
            scrollbar-color: #3498db4d transparent;
            max-height: 635px;
        }

        .message {
            display: flex;
            margin-bottom: 20px;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.chat-right {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin: 0 10px;
            overflow: hidden;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .message-content {
            max-width: 70%;
            display: flex;
            flex-direction: column;
        }

        .message-bubble {
            padding: 12px 18px;
            border-radius: 20px;
            margin-bottom: 5px;
            position: relative;
            word-wrap: break-word;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .message.chat-left .message-bubble {
            background: white;
            color: #2c3e50;
            border-bottom-left-radius: 5px;
        }

        .message.chat-right .message-bubble {
            background: linear-gradient(45deg, #55c7d9, #55c7d9);
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message-time {
            font-size: 11px;
            color: #95a5a6;
            align-self: flex-end;
            margin-top: 2px;
        }

        .message.chat-right .message-time {
            align-self: flex-start;
        }

        .message-image {
            max-width: 250px;
            border-radius: 10px;
            margin-top: 8px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .message-image:hover {
            transform: scale(1.05);
        }

        /* Chat Input */
        .chat-input {
            position: sticky;
            padding: 20px 25px;
            background: white;
            border-top: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 1px solid #3498db4d !important;
            bottom: 0;
            z-index: 10;
        }

        .input-container {
            flex: 1;
            position: relative;
        }

        .message-input {
            width: 100%;
            padding: 12px 50px 12px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .message-input:focus {
            border-color: #55c7d9;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            background: white;
        }

        .file-input-label {
            position: absolute;
            right: 50px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #55c7d9;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .file-input-label:hover {
            background: #55c7d9;
            transform: translateY(-50%) scale(1.1);
        }

        .file-input-label.has-file {
            background: #27ae60;
        }

        .send-btn,
        .clear-btn {
            width: 45px;
            height: 45px;
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .send-btn {
            background: linear-gradient(45deg, #55c7d9, #55c7d9);
        }

        .clear-btn {
            background: linear-gradient(45deg, #95a5a6, #7f8c8d);
        }

        .send-btn:hover,
        .clear-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Welcome Screen */
        .welcome-screen {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #7f8c8d;
        }

        .welcome-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .welcome-text {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .welcome-subtext {
            font-size: 16px;
            opacity: 0.7;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                height: 100vh;
                border-radius: 0;
            }

            .chat-sidebar {
                width: 100%;
                height: 40%;
            }

            .main-chat {
                height: 60%;
            }

            .message-content {
                max-width: 85%;
            }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(52, 152, 219, 0.3);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(52, 152, 219, 0.5);
        }
    </style>
@endpush

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Chat</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Apps</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Chat</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Chat Container -->
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
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                    <button type="button" class="refresh-btn" onclick="userList();">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
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
                            <div class="welcome-text">Welcome to Chat</div>
                            <div class="welcome-subtext">Select a conversation to start messaging</div>
                        </div>

                        <!-- Chat Box -->
                        <div class="main-content-body main-content-body-chat d-none" id="ChatBox">
                            <!-- Chat Header -->
                            <div class="chat-header">
                                <div class="chat-header-avatar" id="ReceiverImage">
                                    <img src="{{ asset('default.jpg') }}" alt="User">
                                </div>
                                <div class="chat-header-info">
                                    <h3 id="ReceiverName" onclick="userChat($('#ReceiverId').val());"
                                        style="cursor: pointer;">User</h3>
                                    <p id="ReceiverRoll">Roll</p>
                                </div>
                                <div class="chat-actions">
                                    <button class="action-btn" onclick="formClear()">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>

                                    <div class="tooltip-container">
                                        <button class="action-btn" id="deleteBtn">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <span class="tooltip-text" id="deleteTooltip"
                                            onclick="confirmDeleteConversation($('#ReceiverId').val());">Delete
                                            Conversation</span>
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
                                        <i class="bi bi-image"></i>
                                    </label>
                                    <input type="file" id="File" style="display: none;"
                                        accept=".jpg,.jpeg,.png,.gif">
                                    <input type="text" style="display: none;" id="ReceiverId" />
                                    <input type="text" style="display: none;" id="RoomId" />
                                </div>
                                <button type="button" class="send-btn" onclick="sendMessage($('#ReceiverId').val())">
                                    <i class="bi bi-send"></i>
                                </button>
                                <button type="button" class="clear-btn" onclick="formClear()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>


    <script>
        function userList() {
            NProgress.start();
            $.ajax({
                url: `{{ route('admin.chat.list') }}`,
                type: "GET",
                success: function(response) {

                    console.log(response)
                    NProgress.done();
                    $('#userList').empty();
                    $.each(response.data.users, function(index, value) {
                        let senderAvatar = value.avatar ? `{{ asset('${value.avatar}') }}` :
                            "{{ asset('default.jpg') }}";
                        let onlineStatus = value.is_online ? 'online' : 'offline';

                        $('#userList').append(`
                        <a class="user-item" href="javascript:void(0)" onclick="userChat(${value.id})" id="selectUser${value.id}">
                            <div class="user-avatar">
                                <img alt="avatar" src="${senderAvatar}">
                                <div class="online-indicator ${onlineStatus}"></div>
                            </div>
                            <div class="user-info">
                                <div class="user-name">${value.f_name}</div>
                                <div class="user-message">${value.last_chat.short_text}</div>
                            </div>
                            <div class="user-time">${value.last_chat.humanize_date}</div>
                        </a>
                    `);
                    });
                },
                error: function(xhr, status, error) {
                    console.log('Error loading users:', error);
                    NProgress.done();
                }
            });
        }

        function userSearch() {
            NProgress.start();
            $('#userList').empty();
            let keyword = $('#keyword').val();


            if (!keyword.trim()) {
                userList();
                return;
            }

            $.ajax({
                url: `{{ route('admin.chat.search') }}?keyword=${keyword}`,
                type: "GET",
                success: function(response) {
                    NProgress.done();

                    // alert(response)
                    $.each(response.data.users, function(index, value) {
                        let senderAvatar = value.avatar ? `{{ asset('${value.avatar}') }}` :
                            "{{ asset('default.jpg') }}";

                        $('#userList').append(`
                        <a class="user-item" href="javascript:void(0)" onclick="userChat(${value.id})" id="selectUser${value.id}">
                            <div class="user-avatar">
                                <img alt="avatar" src="${senderAvatar}">
                            </div>
                            <div class="user-info">
                                <div class="user-name">${value.f_name}</div>
                                <div class="user-message">${value.email}</div>
                            </div>
                        </a>
                    `);
                    });
                },
                error: function(xhr, status, error) {
                    console.log('Error searching users:', error);
                    NProgress.done();
                }
            });
        }

        function userChat(receiver_id) {
            NProgress.start();
            $.ajax({
                url: `{{ route('admin.chat.conversation', ':id') }}`.replace(':id', receiver_id),
                type: "GET",
                success: function(response) {
                    NProgress.done();
                    $('#ChatContent').empty();
                    $('#ReceiverId').val(receiver_id);
                    $('#ReceiverName').text(response.data.receiver.f_name);
                    $('#ReceiverRoll').text(response.data.receiver.role);
                    $('#RoomId').val(response.data.room.id);
                    window.sessionStorage.setItem('room_id', response.data.room.id);

                    // Hide welcome screen and show chat
                    $('#welcomeScreen').hide();
                    $('#ChatBox').removeClass('d-none');

                    // Update selected user
                    $('.user-item').removeClass('selected');
                    $('#selectUser' + receiver_id).addClass('selected');

                    let receiverAvatar = response.data.receiver.avatar ?
                        `{{ asset('${response.data.receiver.avatar}') }}` : "{{ asset('default.jpg') }}";
                    let senderAvatar = response.data.sender.avatar ?
                        `{{ asset('${response.data.sender.avatar}') }}` : "{{ asset('default.jpg') }}";

                    $('#ReceiverImage').html(`<img alt="avatar" src="${receiverAvatar}">`);

                    response.data.chat.forEach(chat => {
                        let chatClass = chat.sender_id == `{{ auth('web')->user()->id }}` ?
                            'message chat-right' : 'message chat-left';
                        let avatar = chat.sender_id == `{{ auth('web')->user()->id }}` ? senderAvatar :
                            receiverAvatar;

                        let messageContent = '';
                        if (chat.text) {
                            messageContent = `<div class="message-bubble">${chat.text}</div>`;
                        }
                        if (chat.file) {
                            messageContent += `<div class="message-bubble">
                            <a href="${chat.file}" target="_blank">
                                <img src="${chat.file}" class="message-image" alt="Image">
                            </a>
                        </div>`;
                        }

                        $('#ChatContent').append(`
                        <div class="${chatClass}">
                            <div class="message-avatar">
                                <img alt="avatar" src="${avatar}">
                            </div>
                            <div class="message-content">
                                ${messageContent}
                                <div class="message-time">${chat.humanize_date}</div>
                            </div>
                        </div>
                    `);
                    });

                    $('#ChatContent').scrollTop($('#ChatContent')[0].scrollHeight);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading conversation:', error);
                    NProgress.done();
                }
            });
        }

        $('#File').on('change', function() {
            let file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#FileLabel').html(
                        `<img src="${e.target.result}" style="width: 20px; height: 20px; border-radius: 3px;"/>`
                    );
                    $('#FileLabel').addClass('has-file');
                };
                reader.readAsDataURL(file);
            }
        });

        function formClear() {
            NProgress.start();
            $('#FileLabel').html(`<i class="bi bi-image"></i>`);
            $('#FileLabel').removeClass('has-file');
            $('#File').val('');
            $('#Text').val('');
            NProgress.done();
            toastr.success('Form cleared successfully!');
        }

        function sendMessage(receiver_id) {
            NProgress.start();
            let text = $('#Text').val() || null;
            let file = $('#File')[0].files[0] || null;

            if (text !== null || file !== null) {
                let formData = new FormData();
                if (text !== null) {
                    formData.append('text', text);
                }
                if (file !== null) {
                    formData.append('file', file);
                }

                $.ajax({
                    url: `{{ route('admin.chat.send', ':id') }}`.replace(':id', receiver_id),
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        NProgress.done();
                        $('#Text').val('');
                        $('#File').val('');
                        $('#FileLabel').html(`<i class="bi bi-image"></i>`);
                        $('#FileLabel').removeClass('has-file');
                        userChat(receiver_id);
                        userList();
                        toastr.success('Message sent successfully!');
                    },
                    error: function(xhr, status, error) {
                        console.log('Error sending message:', error);
                        NProgress.done();
                        toastr.error('Failed to send message');
                    }
                });
            } else {
                NProgress.done();
                toastr.warning('Please enter a message or select a file');
            }
        }

        // Enter key to send message
        $('#Text').on('keypress', function(e) {
            if (e.which === 13) {
                let receiverId = $('#ReceiverId').val();
                if (receiverId) {
                    sendMessage(receiverId);
                }
            }
        });

        // Search on Enter key
        $('#keyword').on('keypress', function(e) {
            if (e.which === 13) {
                userSearch();
            }
        });

        // Auto-refresh user list every 5 minutes
        setInterval(() => {
            userList();
        }, 300000);

        // Initialize
        userList();


        //delete conversation tooltrip
        document.getElementById('deleteBtn').addEventListener('click', function(e) {
            e.stopPropagation(); // stop from closing instantly
            let tooltip = document.getElementById('deleteTooltip');

            // Toggle tooltip
            tooltip.classList.toggle('show');
        });

        // Hide tooltip when clicking anywhere else
        document.addEventListener('click', function() {
            document.getElementById('deleteTooltip').classList.remove('show');
        });

        // Click tooltip → SweetAlert
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
                    fetch(`/admin/chat/conversation/delete/${receiverId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Deleted!', data.message, 'success');
                                userList();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                }
            });
        }
    </script>


<<<<<<< HEAD
    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        //     Echo.private(`chat-receiver.{{ auth('web')->user()->id }}`)
        //         .listen('MessageSendEvent', function(e) {
        //             console.log('Received event:', e); // Debugging er jonno

        //             // Eikhane data structure change korun
        //             toastr.success(e.chat.text ?? "New file received");
        //             alert('Message received: ' + (e.chat.text || "File"));

        //             let receiver_id = document.getElementById('ReceiverId').value;
        //             if (receiver_id) {
        //                 userChat(receiver_id);
        //             }
        //             userList();
        //         });
        // });


        var user_id = `{{ auth('web')->check() ? auth('web')->user()->id : null }}`;

        if (user_id) {
            document.addEventListener('DOMContentLoaded', function() {
                Echo.private(`chat-receiver.${user_id}`).listen('MessageSendEvent', function(e) {
                    toastr.success(e.data.text ?? "File Sent");
                    let receiver_id = document.getElementById('ReceiverId').value;
=======
        // Laravel Echo for real-time messaging
        document.addEventListener('DOMContentLoaded', function() {
            Echo.private(`chat-receiver.{{ auth('web')->user()->id }}`).listen('MessageSendEvent', function(e) {
                toastr.success(e.data.text ?? "New file received");
                // alert('get');
                let receiver_id = document.getElementById('ReceiverId').value;
                if (receiver_id) {
>>>>>>> ariful
                    userChat(receiver_id);
                    userList();
                });
            });
        }
    </script>
@endpush
