<?php 
// https://github.com/tgallice/fb-messenger-sdk

namespace CestaBot;
use Tgallice\FBMessenger\WebhookRequestHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tgallice\FBMessenger\Callback\MessageEvent;

class QuickReplies implements EventSubscriberInterface {

    public static function getSubscribedEvents() {
        
        return [
            MessageEvent::NAME => 'onMessageEvent', // may also handle quick replies by checking with isQuickReply()
            PostbackEvent::NAME => 'onPostbackEvent',
            'DEVELOPER_DEFINED_PAYLOAD_1' => 'onQuickReply' // optional: quick reply specific payload
        ];
    }

    public function onMessageEvent(MessageEvent $event) {
        if( $event->isQuickReply() ) { // if a quick reply callback, pass it to our onQuickReply method
            $this->onQuickReply($event);
            return;
        }
		
        print(__METHOD__."\n");
    }

    public function onPostbackEvent(PostbackEvent $event) {
        print(__METHOD__."\n");
    }

    public function onQuickReply(MessageEvent $event) {
        switch( $event->getQuickReplyPayload() ) {
            case 'DEVELOPER_DEFINED_PAYLOAD_2':
                print(__METHOD__."\n");
                break;
        }
    }
}