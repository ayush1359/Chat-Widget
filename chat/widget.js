$(document).ready(function() {
    $('.helpwise-home-screen-start-conversation-card').removeClass('d-none');
});

$('.helpwise-see-all-link').on('click', function() {
    $('.helpwise-home-screen-start-conversation-card').addClass('d-none');
    showconversations(user_id, mailbox_id);
});

$('.new-conversation-button').on('click', function() {
    $('.helpwise-home-screen-start-conversation-card').addClass('d-none');
    $('.chat_widget_show_conversation').addClass('d-none');
    $('.chat_widget_new_conversation').removeClass('d-none');
});

$('.back_show_home_page').on('click', function() {
    $('.chat_widget_conversation_list').html('');
    $('.helpwise-home-screen-start-conversation-card').removeClass('d-none');
    $('.chat_widget_show_conversation').addClass('d-none');
});

$('#send-new-conversation-message').on('click', function() {
    var conversation_id = '';
    let value = $('#send-new-conversation-message-input').val();
    $('.create_new_conversation').append(`<ul class="p-0">
        <div>
        <li class="list-group-item d-flex justify-content-end" style="background-color:#0384fc; width :60%;">
            <span style="color:#0a0a0a; font-size:15px;" class="d-block tx-11">${value}</span>  
        </li>
        </div>
            </ul>`);
    $('input[name=inputmessage]').val("");
    $('.chat_widget_new_conversation').addClass('d-none');
    conversation_id = createchat(user_id, mailbox_id, conversation_id, value);
    showmessages(user_id, mailbox_id, conversation_id);
});

function showconversations(user_id, mailbox_id) {
    $.ajax({
        url: '/chat/conversations.php',
        data: {
            user_id,
            mailbox_id
        }
    }).done(response => {
        if (response.status == 'success') {
            if (response.data.length == 0) {
                $('.chat_widget_show_conversation').removeClass('d-none');
                //$('.chat_widget_new_conversation').addClass('d-none');
                //$('.chat_widget_show_messages').addClass('d-none');
            } else {
                showconversationsUI(response.data);
                $('.helpwise-home-screen-start-conversation-card').addClass('d-none');
                $('.chat_widget_show_conversation').removeClass('d-none');
            }
        } else {}
    }).fail(() => {});
}

function showconversationsUI(data) {
    var n = data.length;
    if (n > 0) {
        for (var i = 0; i < n; i++) {
            var conversation = data[i];
            let {
                conversation_id,
                body,
                date_time
            } = conversation;
            var d = moment(date_time * 1000).fromNow();
            $('.chat_widget_conversation_list').append(`<div class="card chat_widget_conversation" id="${conversation_id}" style="width: 18rem; padding: 0px; margin:20px;">
        <div class="card-body">
        <div class="card-text" style="font-size:14px;"><span>${body}<span>
        <span style="float:right; font-size:11px;">${d}</span</div>
        </div>
        </div>`);
        }
    }
}

$(document).on('click', '.chat_widget_conversation', function() {
    var conversation_id = this.id;
    $('.chat_widget_show_conversation').addClass('d-none');
    showmessages(user_id, mailbox_id, conversation_id);
});



function showmessages(user_id, mailbox_id, conversation_id) {
    $.ajax({
        url: '/chat/messages.php',
        data: {
            user_id,
            mailbox_id,
            conversation_id
        }
    }).done(response => {
        if (response.status == 'success') {
            if (response.data == 0) {
                $('.chat_widget_show_conversation').removeClass('d-none');
            } else {
                $('.chat_widget_show_messages').removeClass('d-none');
                makemessagesUI(response.data, conversation_id);
            }
        } else {

        }
    }).fail(() => {});
}

$('.back_show_conversation_list').on('click', function() {
    $('.create_new_conversation').html('');
    $('.chat_widget_conversation_list').html('');
    $('.conversation_messages_box').html('');
    $('.chat_widget_new_conversation').addClass('d-none');
    $('.chat_widget_show_messages').addClass('d-none');
    showconversations(user_id, mailbox_id);
});

function makemessagesUI(data, conversation_id) {
    var n = data.length;
    if (n > 0) {
        for (var i = 0; i < n; i++) {
            var j = i + 1;
            var message = data[i];
            let {
                id,
                body,
                date_time,
                type
            } = message;
            var d = moment(date_time * 1000).fromNow();
            let alignment = "";
            let color = "";
            if (type == 1) {
                alignment = "justify-content-start";
                color = "#d0d8db";
            } else {
                alignment = "justify-content-end";
                color = "#4ec3f5";
            }
            $('.conversation_messages_box').append(`<ul class="p-0">
        <div class="d-flex ${alignment}">
        <li class="list-group-item"  style=" background-color:${color}; 
        word-wrap : break-word; width :60%; display: flex; flex-direction: column;">    
            <span style="color:#0a0a0a; font-size:15px; width:100%;" class="d-block tx-11">${body}</span>
            <div class = "d-flex justify-content-end">
            <span style="font-size:11px;">${d}</span>
            </div>
        </li>       
        </div>
        </ul>`);
        }
    }

    $('#send-existing-conversation-message').off('click').on('click', function() {
        let e = moment().fromNow();
        let value = $('#send-existing-conversation-message-input').val();
        $('.conversation_messages_box').append(`<ul class="p-0">
        <div class="d-flex justify-content-end">
        <li class="list-group-item d-flex justify-content-start" style="background-color:#4ec3f5; width :60%;
         display: flex; flex-direction: column;">
            <span style="color:#0a0a0a; font-size:15px; width :100%;" class="d-block tx-11">${value}</span>
            <div class = "d-flex justify-content-end">
            <span style="font-size:11px;">${e}</span>
            </div>  
            </li>
            </div>
            </ul>`);
        $('input[name=inputmessage]').val("");
        createchat(user_id, mailbox_id, conversation_id, value);
        $(".latest-message-time").remove();
    });
}


$('#conversation-list').on('click', function() {
    $('.chat_widget_show_conversation').addClass('d-none');
    $('.chat_widget_show_messages').removeClass('d-none');
});


function createchat(user_id, mailbox_id, conversation_id, value) {
    var a;
    $.ajax({
        'async': false,
        'url': '/chat/send_message.php',
        'data': {
            user_id,
            mailbox_id,
            conversation_id,
            value
        },
        'success': function(response) {
            a = response.data;
        }
    });
    return a;
}