@extends('backend.app')

@section('title', 'Group Chat')
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
        .refresh-btn,
        .create-group-btn {
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

        .create-group-btn {
            background: linear-gradient(45deg, #27ae60, #229954);
            width: 100%;
            margin-top: 10px;
        }

        .search-btn:hover,
        .refresh-btn:hover,
        .create-group-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Group List */
        .group-list {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #3498db4d transparent;
            max-height: calc(100vh - 150px);
            padding-right: 5px;
        }

        .group-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            text-decoration: none;
            color: #2c3e50;
        }

        .group-item:hover {
            background: rgba(52, 152, 219, 0.1);
            transform: translateX(5px);
            text-decoration: none;
        }

        .group-item.selected {
            background: linear-gradient(90deg, rgba(52, 152, 219, 0.3), rgba(41, 128, 185, 0.3));
            border-left: 4px solid #55c7d9;
        }

        .group-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(52, 152, 219, 0.3);
        }

        .group-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .unread-badge {
            position: absolute;
            top: -5px;
            right: 5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }

        .group-info {
            flex: 1;
        }

        .group-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .group-message {
            font-size: 13px;
            color: #7f8c8d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }

        .group-time {
            font-size: 12px;
            color: rgba(26, 25, 25, 0.5);
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .member-count {
            font-size: 11px;
            color: #95a5a6;
            margin-top: 2px;
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

        .message-sender-name {
            font-size: 12px;
            font-weight: 600;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .message.chat-right .message-sender-name {
            text-align: right;
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

        /* Modal Styles */
        .modal-backdrop.show {
            opacity: 0.7;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(45deg, #55c7d9, #55c7d9);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }

        .modal-body {
            padding: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #55c7d9;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn-primary {
            background: linear-gradient(45deg, #55c7d9, #55c7d9);
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(45deg, #95a5a6, #7f8c8d);
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
        }

        .member-tag {
            display: inline-block;
            background: #e9ecef;
            padding: 5px 12px;
            border-radius: 15px;
            margin: 3px;
            font-size: 13px;
        }

        .member-tag .remove-member {
            margin-left: 8px;
            color: #e74c3c;
            cursor: pointer;
            font-weight: bold;
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
    </style>
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Group Chat</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Apps</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Group Chat</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Chat Container -->
                <div class="chat-container">
                    <!-- Sidebar -->
                    <div class="chat-sidebar">
                        <div class="sidebar-header">
                            <h3><i class="bi bi-people-fill"></i> Groups</h3>
                            <div class="search-container">
                                <input name="keyword" type="text" id="keyword" class="search-input"
                                    placeholder="Search groups...">
                                <div class="search-actions">
                                    <button type="button" class="search-btn" onclick="searchGroups();">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                    <button type="button" class="refresh-btn" onclick="loadGroupList();">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                </div>
                                <button type="button" class="create-group-btn" data-bs-toggle="modal"
                                    data-bs-target="#createGroupModal">
                                    <i class="bi bi-plus-circle"></i> Create New Group
                                </button>
                            </div>
                        </div>
                        <div class="group-list" id="groupList">
                            <!-- Groups will be populated here -->
                        </div>
                    </div>

                    <!-- Main Chat Area -->
                    <div class="main-chat">
                        <!-- Welcome Screen -->
                        <div class="welcome-screen" id="welcomeScreen">
                            <div class="welcome-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="welcome-text">Welcome to Group Chat</div>
                            <div class="welcome-subtext">Select a group to start messaging</div>
                        </div>

                        <!-- Chat Box -->
                        <div class="main-content-body main-content-body-chat d-none" id="ChatBox">
                            <!-- Chat Header -->
                            <div class="chat-header">
                                <div class="chat-header-avatar" id="GroupImage">
                                    <img src="{{ asset('default/default_image.jpg') }}" alt="Group">
                                </div>
                                <div class="chat-header-info">
                                    <h3 id="GroupName" onclick="showGroupDetails();">Group Name</h3>
                                    <p id="GroupMembers">0 members</p>
                                </div>
                                <div class="chat-actions">
                                    <button class="action-btn" onclick="showGroupDetails();" title="Group Info">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                    <button class="action-btn" onclick="loadGroupMessages($('#CurrentGroupId').val());"
                                        title="Refresh">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <button class="action-btn" onclick="showGroupSettings();" title="Settings">
                                        <i class="bi bi-gear"></i>
                                    </button>
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
                                        id="MessageText">
                                    <label for="MessageFile" id="FileLabel" class="file-input-label">
                                        <i class="bi bi-image"></i>
                                    </label>
                                    <input type="file" id="MessageFile" style="display: none;">
                                    <input type="hidden" id="CurrentGroupId" />
                                </div>
                                <button type="button" class="send-btn" onclick="sendGroupMessage()">
                                    <i class="bi bi-send"></i>
                                </button>
                                <button type="button" class="clear-btn" onclick="clearMessageForm()">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Group Modal -->
    <div class="modal fade" id="createGroupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Create New Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createGroupForm">
                        <div class="mb-3">
                            <label class="form-label">Group Name *</label>
                            <input type="text" class="form-control" id="groupName" placeholder="Enter group name"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="groupDescription" rows="3" placeholder="Group description (optional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Group Avatar</label>
                            <input type="file" class="form-control" id="groupAvatar" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Add Members *</label>
                            <select class="form-control" id="groupMembers" multiple style="height: 150px;">
                                <!-- Users will be populated here -->
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple members</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="createGroup()">Create Group</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Details Modal -->
    <div class="modal fade" id="groupDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-info-circle"></i> Group Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="groupDetailsContent">
                    <!-- Group details will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Group Settings Modal -->
    <div class="modal fade" id="groupSettingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-gear"></i> Group Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateGroupForm">
                        <div class="mb-3">
                            <label class="form-label">Group Name</label>
                            <input type="text" class="form-control" id="updateGroupName">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="updateGroupDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Change Avatar</label>
                            <input type="file" class="form-control" id="updateGroupAvatar" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-danger btn-sm" onclick="leaveGroup()">
                                <i class="bi bi-box-arrow-right"></i> Leave Group
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteGroup()">
                                <i class="bi bi-trash"></i> Delete Group
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateGroup()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>

    <script>
        // Complete Fixed JavaScript for Group Chat

        // Global variables
        let currentGroupId = null;
        let messagePollingInterval = null;

        // Load group list - FIXED
        function loadGroupList() {
            NProgress.start();
            $.ajax({
                url: '/admin/group/list',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    NProgress.done();
                    $('#groupList').empty();

                    console.log('Groups loaded:', response);

                    try {
                        if (response.success && response.groups && response.groups.length > 0) {
                            $.each(response.groups, function(index, group) {
                                let groupAvatar = group.avatar || '/default/default_image.jpg';
                                let unreadBadge = group.unread_count > 0 ?
                                    `<div class="unread-badge">${group.unread_count}</div>` : '';

                                let lastMessageText = 'No messages yet';
                                if (group.last_message) {
                                    lastMessageText = group.last_message.text || 'File';
                                }

                                let lastMessageTime = group.last_message ? group.last_message
                                    .relative_time : '';

                                $('#groupList').append(`
                            <a class="group-item" href="javascript:void(0)" onclick="openGroup(${group.id})" id="group${group.id}">
                                <div class="group-avatar">
                                    <img alt="group" src="${groupAvatar}">
                                    ${unreadBadge}
                                </div>
                                <div class="group-info">
                                    <div class="group-name">${group.name}</div>
                                    <div class="group-message">${lastMessageText}</div>
                                    <div class="member-count">${group.member_count} members</div>
                                </div>
                                <div class="group-time">${lastMessageTime}</div>
                            </a>
                        `);
                            });
                        } else {
                            $('#groupList').html(
                                '<div class="text-center p-4 text-muted">No groups found. Create one to get started!</div>'
                            );
                        }
                    } catch (error) {
                        console.error('Error processing groups:', error);
                        toastr.error('Failed to process group data');
                    }
                },
                error: function(xhr) {
                    NProgress.done();
                    console.error('Error loading groups:', xhr);
                    toastr.error('Failed to load groups');
                }
            });
        }

        // Search groups
        function searchGroups() {
            let keyword = $('#keyword').val().trim();
            if (!keyword) {
                loadGroupList();
                return;
            }

            NProgress.start();
            $.ajax({
                url: `/admin/group/list?keyword=${encodeURIComponent(keyword)}`,
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    NProgress.done();
                    $('#groupList').empty();

                    if (response.success && response.groups && response.groups.length > 0) {
                        $.each(response.groups, function(index, group) {
                            let groupAvatar = group.avatar || '/default/default_image.jpg';

                            $('#groupList').append(`
                        <a class="group-item" href="javascript:void(0)" onclick="openGroup(${group.id})" id="group${group.id}">
                            <div class="group-avatar">
                                <img alt="group" src="${groupAvatar}">
                            </div>
                            <div class="group-info">
                                <div class="group-name">${group.name}</div>
                                <div class="member-count">${group.member_count} members</div>
                            </div>
                        </a>
                    `);
                        });
                    } else {
                        $('#groupList').html('<div class="text-center p-4 text-muted">No groups found</div>');
                    }
                },
                error: function(xhr) {
                    NProgress.done();
                    console.error('Error searching groups:', xhr);
                    toastr.error('Failed to search groups');
                }
            });
        }

        // Open group chat - FIXED
        function openGroup(groupId) {
            NProgress.start();
            $.ajax({
                url: `/admin/group/${groupId}`,
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    NProgress.done();

                    if (!response.success || !response.data || !response.data.group) {
                        toastr.error('Invalid group data');
                        return;
                    }

                    let group = response.data.group;
                    currentGroupId = groupId;

                    $('#CurrentGroupId').val(groupId);
                    $('#GroupName').text(group.name);
                    $('#GroupMembers').text(`${group.member_count} members`);

                    let groupAvatar = group.avatar || '/default/default_image.jpg';
                    $('#GroupImage').html(`<img src="${groupAvatar}" alt="Group">`);

                    $('#welcomeScreen').hide();
                    $('#ChatBox').removeClass('d-none');

                    $('.group-item').removeClass('selected');
                    $(`#group${groupId}`).addClass('selected');

                    loadGroupMessages(groupId);
                    markAsRead(groupId);
                },
                error: function(xhr) {
                    NProgress.done();
                    console.error('Error opening group:', xhr);
                    toastr.error('Failed to load group');
                }
            });
        }

        // Load group messages - FIXED
        function loadGroupMessages(groupId) {
            NProgress.start();
            $.ajax({
                url: `/admin/group/${groupId}/messages`,
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    NProgress.done();
                    $('#ChatContent').empty();

                    let currentUserId = {{ auth('web')->user()->id ?? 'null' }};

                    console.log('Messages loaded:', response);

                    if (response.success && response.data && response.data.messages && response.data.messages
                        .length > 0) {
                        $.each(response.data.messages, function(index, message) {
                            let isCurrentUser = message.sender_id === currentUserId;
                            let chatClass = isCurrentUser ? 'message chat-right' : 'message chat-left';

                            let senderAvatar = message.sender && message.sender.avatar ?
                                message.sender.avatar : '/default/default_image.jpg';

                            let messageContent = '';
                            if (message.text) {
                                messageContent =
                                    `<div class="message-bubble">${escapeHtml(message.text)}</div>`;
                            }
                            if (message.file) {
                                messageContent += `<div class="message-bubble">
                            <a href="${message.file}" target="_blank">
                                <img src="${message.file}" class="message-image" alt="File" onerror="this.style.display='none'; this.parentElement.innerHTML='📎 File';">
                            </a>
                        </div>`;
                            }

                            let senderName = isCurrentUser ? 'You' :
                                `${message.sender.first_name || ''} ${message.sender.last_name || ''}`
                                .trim();

                            $('#ChatContent').append(`
                        <div class="${chatClass}">
                            <div class="message-avatar">
                                <img alt="avatar" src="${senderAvatar}">
                            </div>
                            <div class="message-content">
                                <div class="message-sender-name">${senderName}</div>
                                ${messageContent}
                                <div class="message-time">${message.created_at || ''}</div>
                            </div>
                        </div>
                    `);
                        });
                    } else {
                        $('#ChatContent').html(
                            '<div class="text-center p-4 text-muted">No messages yet. Start the conversation!</div>'
                        );
                    }

                    // Scroll to bottom
                    setTimeout(() => {
                        $('#ChatContent').scrollTop($('#ChatContent')[0].scrollHeight);
                    }, 100);
                },
                error: function(xhr) {
                    NProgress.done();
                    console.error('Error loading messages:', xhr);
                    toastr.error('Failed to load messages');
                }
            });
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Send group message - FIXED
        function sendGroupMessage() {
            let groupId = $('#CurrentGroupId').val();
            if (!groupId) {
                toastr.warning('Please select a group first');
                return;
            }

            let text = $('#MessageText').val().trim();
            let file = $('#MessageFile')[0].files[0];

            if (!text && !file) {
                toastr.warning('Please enter a message or select a file');
                return;
            }

            NProgress.start();
            let formData = new FormData();
            if (text) formData.append('text', text);
            if (file) formData.append('file', file);

            $.ajax({
                url: `/admin/group/${groupId}/send`,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    NProgress.done();
                    clearMessageForm();
                    loadGroupMessages(groupId);
                    loadGroupList();
                    toastr.success('Message sent successfully');
                },
                error: function(xhr) {
                    NProgress.done();
                    console.error('Error sending message:', xhr);
                    toastr.error(xhr.responseJSON?.message || 'Failed to send message');
                }
            });
        }

        // Clear message form
        function clearMessageForm() {
            $('#MessageText').val('');
            $('#MessageFile').val('');
            $('#FileLabel').html(`<i class="bi bi-image"></i>`);
            $('#FileLabel').removeClass('has-file');
        }

        // Mark messages as read
        function markAsRead(groupId) {
            $.ajax({
                url: `/admin/group/${groupId}/read`,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    // Refresh group list to update unread counts
                    loadGroupList();
                },
                error: function(xhr) {
                    console.error('Error marking as read:', xhr);
                }
            });
        }

        // Load users for group creation
        function loadUsersForGroup() {
            $.ajax({
                url: `/admin/group/users/list`,
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#groupMembers').empty();
                    if (response.success && response.users) {
                        $.each(response.users, function(index, user) {
                            $('#groupMembers').append(
                                `<option value="${user.id}">${user.first_name} ${user.last_name} (${user.email})</option>`
                            );
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error loading users:', xhr);
                    toastr.error('Failed to load users');
                }
            });
        }

        // Create group
        function createGroup() {
            let name = $('#groupName').val().trim();
            let description = $('#groupDescription').val().trim();
            let avatar = $('#groupAvatar')[0].files[0];
            let members = $('#groupMembers').val();

            if (!name) {
                toastr.warning('Please enter group name');
                return;
            }

            if (!members || members.length === 0) {
                toastr.warning('Please select at least one member');
                return;
            }

            NProgress.start();
            let formData = new FormData();
            formData.append('name', name);
            if (description) formData.append('description', description);
            if (avatar) formData.append('avatar', avatar);
            members.forEach(memberId => {
                formData.append('members[]', memberId);
            });

            $.ajax({
                url: `/admin/group/create`,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    NProgress.done();
                    $('#createGroupModal').modal('hide');
                    $('#createGroupForm')[0].reset();
                    loadGroupList();
                    toastr.success('Group created successfully');
                },
                error: function(xhr) {
                    NProgress.done();
                    console.error('Error creating group:', xhr);
                    toastr.error(xhr.responseJSON?.message || 'Failed to create group');
                }
            });
        }

        // Show group details
        function showGroupDetails() {
            let groupId = $('#CurrentGroupId').val();
            if (!groupId) return;

            NProgress.start();
            $.ajax({
                url: `/admin/group/${groupId}`,
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    NProgress.done();

                    if (!response.success || !response.data || !response.data.group) {
                        toastr.error('Failed to load group details');
                        return;
                    }

                    let group = response.data.group;
                    let groupAvatar = group.avatar || '/default/default_image.jpg';

                    let membersHtml = '';
                    if (group.members && group.members.length > 0) {
                        group.members.forEach(member => {
                            let memberAvatar = member.user && member.user.avatar ?
                                member.user.avatar : '/default/default_image.jpg';
                            let roleBadge = member.role === 'admin' ?
                                '<span class="badge bg-primary">Admin</span>' :
                                '<span class="badge bg-secondary">Member</span>';

                            let userName = member.user ?
                                `${member.user.first_name || ''} ${member.user.last_name || ''}`
                                .trim() : 'Unknown';
                            let userEmail = member.user ? member.user.email : '';

                            membersHtml += `
                        <div class="d-flex align-items-center mb-3 p-2 border-bottom">
                            <img src="${memberAvatar}" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <strong>${userName}</strong>
                                <div class="small text-muted">${userEmail}</div>
                            </div>
                            ${roleBadge}
                        </div>
                    `;
                        });
                    }

                    let creatorName = group.creator ?
                        `${group.creator.first_name || ''} ${group.creator.last_name || ''}`.trim() : 'Unknown';

                    $('#groupDetailsContent').html(`
                <div class="text-center mb-4">
                    <img src="${groupAvatar}" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                    <h4>${group.name}</h4>
                    <p class="text-muted">${group.description || 'No description'}</p>
                    <small class="text-muted">Created by ${creatorName}</small>
                </div>
                <hr>
                <h6 class="mb-3">Members (${group.member_count})</h6>
                <div style="max-height: 300px; overflow-y: auto;">
                    ${membersHtml}
                </div>
            `);

                    $('#groupDetailsModal').modal('show');
                },
                error: function(xhr) {
                    NProgress.done();
                    console.error('Error loading group details:', xhr);
                    toastr.error('Failed to load group details');
                }
            });
        }

        // Show group settings
        function showGroupSettings() {
            let groupId = $('#CurrentGroupId').val();
            if (!groupId) return;

            $.ajax({
                url: `/admin/group/${groupId}`,
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.data && response.data.group) {
                        let group = response.data.group;
                        $('#updateGroupName').val(group.name);
                        $('#updateGroupDescription').val(group.description || '');
                        $('#groupSettingsModal').modal('show');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading group settings:', xhr);
                    toastr.error('Failed to load group settings');
                }
            });
        }

        // Update group
        function updateGroup() {
            let groupId = $('#CurrentGroupId').val();
            let name = $('#updateGroupName').val().trim();
            let description = $('#updateGroupDescription').val().trim();
            let avatar = $('#updateGroupAvatar')[0].files[0];

            if (!name) {
                toastr.warning('Please enter group name');
                return;
            }

            NProgress.start();
            let formData = new FormData();
            formData.append('name', name);
            formData.append('description', description);
            if (avatar) formData.append('avatar', avatar);

            $.ajax({
                url: `/admin/group/${groupId}/update`,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    NProgress.done();
                    $('#groupSettingsModal').modal('hide');
                    loadGroupList();
                    openGroup(groupId);
                    toastr.success('Group updated successfully');
                },
                error: function(xhr) {
                    NProgress.done();
                    console.error('Error updating group:', xhr);
                    toastr.error('Failed to update group');
                }
            });
        }

        // Leave group
        function leaveGroup() {
            let groupId = $('#CurrentGroupId').val();

            Swal.fire({
                title: 'Leave Group?',
                text: "Are you sure you want to leave this group?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Yes, leave it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    NProgress.start();
                    $.ajax({
                        url: `/admin/group/${groupId}/leave`,
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            NProgress.done();
                            $('#groupSettingsModal').modal('hide');
                            $('#ChatBox').addClass('d-none');
                            $('#welcomeScreen').show();
                            currentGroupId = null;
                            loadGroupList();
                            toastr.success('Left group successfully');
                        },
                        error: function(xhr) {
                            NProgress.done();
                            console.error('Error leaving group:', xhr);
                            toastr.error(xhr.responseJSON?.message || 'Failed to leave group');
                        }
                    });
                }
            });
        }

        // Delete group
        function deleteGroup() {
            let groupId = $('#CurrentGroupId').val();

            Swal.fire({
                title: 'Delete Group?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    NProgress.start();
                    $.ajax({
                        url: `/admin/group/${groupId}/delete`,
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            NProgress.done();
                            $('#groupSettingsModal').modal('hide');
                            $('#ChatBox').addClass('d-none');
                            $('#welcomeScreen').show();
                            currentGroupId = null;
                            loadGroupList();
                            toastr.success('Group deleted successfully');
                        },
                        error: function(xhr) {
                            NProgress.done();
                            console.error('Error deleting group:', xhr);
                            toastr.error(xhr.responseJSON?.message || 'Failed to delete group');
                        }
                    });
                }
            });
        }

        // File input handling
        $('#MessageFile').on('change', function() {
            let file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#FileLabel').html(
                        `<img src="${e.target.result}" style="width: 20px; height: 20px; border-radius: 3px; object-fit: cover;"/>`
                    );
                    $('#FileLabel').addClass('has-file');
                };
                reader.readAsDataURL(file);
            }
        });

        // Enter key to send message
        $('#MessageText').on('keypress', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                sendGroupMessage();
            }
        });

        // Enter key to search
        $('#keyword').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                searchGroups();
            }
        });

        // Load users when create modal opens
        $('#createGroupModal').on('show.bs.modal', function() {
            loadUsersForGroup();
        });

        // Initialize on document ready
        $(document).ready(function() {
            console.log('Initializing group chat...');
            loadGroupList();
        });


        // Real-time message listening with Laravel Echo
        let userId = {{ auth('web')->user()->id ?? 'null' }};

        console.log('🔍 User ID:', userId);
        console.log('🔍 Echo available:', typeof Echo !== 'undefined');

        if (userId && typeof Echo !== 'undefined') {
            document.addEventListener('DOMContentLoaded', function() {
                console.log('✅ Setting up Echo listeners for user:', userId);

                Echo.private(`group-message.${userId}`)
                    .listen('GroupMessageSendEvent', function(e) {
                        console.log('🎉 Received group message event:', e);
                        console.log('📨 Message data:', e.message);

                        if (e.message) {
                            let messageGroupId = e.message.group_id;

                            // Show notification
                            if (e.message.group && e.message.group.name) {
                                console.log('📢 Showing notification for:', e.message.group.name);
                                toastr.info('New message in ' + e.message.group.name);
                            }

                            // If current group is open, reload messages
                            if (currentGroupId == messageGroupId) {
                                console.log('🔄 Reloading messages for current group:', messageGroupId);
                                loadGroupMessages(currentGroupId);
                                markAsRead(currentGroupId);
                            } else {
                                console.log('ℹ️ Message for different group. Current:', currentGroupId,
                                    'Message group:', messageGroupId);
                            }

                            // Always refresh group list
                            console.log('🔄 Refreshing group list');
                            loadGroupList();
                        }
                    })
                    .error(function(error) {
                        console.error('❌ Echo error:', error);
                    });

                console.log('✅ Echo listeners setup complete for channel: group-message.' + userId);
            });
        } else {
            if (!userId) {
                console.warn('⚠️ User not authenticated');
            }
            if (typeof Echo === 'undefined') {
                console.warn('⚠️ Echo not available');
            }
        }

        // var userId = `{{ auth('web')->check() ? auth('web')->user()->id : null }}`;

        // if (userId) {
        //     document.addEventListener('DOMContentLoaded', function() {
        //         Echo.private(`group-message.${userId}`)
        //             .listen('GroupMessageSendEvent', function(e) {
        //                 console.log('Received event:', e);
        //                 toastr.success(e.data.text ?? "New file received");
        //                 let receiver_id = document.getElementById('ReceiverId').value;
        //                 if (receiver_id) {
        //                     userChat(receiver_id);
        //                     userList();
        //                 }
        //             });
        //     });
        // }
    </script>

    {{-- <script>
        // ✅ COMPLETE GROUP CHAT ECHO SETUP
        let userId = {{ auth('web')->user()->id ?? 'null' }};

        console.log('🚀 Initializing Group Chat Echo');
        console.log('👤 User ID:', userId);
        console.log('🔌 Echo available:', typeof Echo !== 'undefined');

        if (userId && typeof Echo !== 'undefined') {

            // Wait for DOM to be ready
            document.addEventListener('DOMContentLoaded', function() {
                console.log('📡 Setting up Echo listener...');

                // Test Echo connection first
                Echo.connector.pusher.connection.bind('state_change', function(states) {
                    console.log('🔄 Pusher state:', states.current);
                });

                Echo.connector.pusher.connection.bind('connected', function() {
                    console.log('✅ Pusher connected successfully');
                    console.log('🎯 Subscribing to: group-message.' + userId);
                });

                // Subscribe to private channel
                const channel = Echo.private(`group-message.${userId}`);

                // Check subscription success
                channel.subscribed(() => {
                    console.log('✅ Successfully subscribed to group-message.' + userId);
                });

                channel.error((error) => {
                    console.error('❌ Subscription error:', error);
                    console.error(
                        '💡 Check: 1) Broadcasting routes enabled 2) CSRF token present 3) User authenticated'
                        );
                });

                // Listen for group messages
                channel.listen('GroupMessageSendEvent', function(e) {
                    console.log('🎉 GROUP MESSAGE RECEIVED!');
                    console.log('📨 Full event data:', e);

                    if (!e.message) {
                        console.warn('⚠️ No message data in event');
                        return;
                    }

                    let messageGroupId = e.message.group_id;
                    let senderId = e.message.sender_id;

                    console.log('📍 Message group:', messageGroupId);
                    console.log('📍 Current group:', currentGroupId);
                    console.log('👤 Sender:', senderId, '| Current user:', userId);

                    // Don't process own messages
                    if (senderId !== userId) {
                        // Show notification
                        if (e.message.group && e.message.group.name) {
                            let senderName = e.message.sender ?
                                `${e.message.sender.first_name} ${e.message.sender.last_name}`.trim() :
                                'Someone';

                            toastr.info(`${senderName} sent a message in ${e.message.group.name}`);
                        }
                    }

                    // If viewing this group, reload messages
                    if (currentGroupId == messageGroupId) {
                        console.log('🔄 Reloading messages for current group');
                        loadGroupMessages(currentGroupId);

                        if (senderId !== userId) {
                            markAsRead(currentGroupId);
                        }
                    }

                    // Always refresh group list
                    console.log('🔄 Refreshing group list');
                    loadGroupList();
                });

                console.log('✅ Echo listener setup complete');
            });

        } else {
            if (!userId) {
                console.error('❌ User not authenticated');
            }
            if (typeof Echo === 'undefined') {
                console.error('❌ Echo not loaded. Check app.js is imported.');
            }
        }
    </script> --}}
@endpush
