import Echo from "laravel-echo";

import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],


});
//   window.Echo.private(`group-message.2`)
//   alert(userId)
//                     .listen('GroupMessageSendEvent', function(e) {

//                         console.log('Received event:', e);
//                         toastr.success(e.data.text ?? "New file received");
//                         let receiver_id = document.getElementById('ReceiverId').value;
//                         if (receiver_id) {
//                             userChat(receiver_id);
//                             userList();
//                         }
//                     });
// Debug connection
window.Echo.connector.pusher.connection.bind('connected', function() {
    console.log('✅ Pusher Connected');
});

window.Echo.connector.pusher.connection.bind('error', function(err) {
    console.error('❌ Pusher Error:', err);
});
