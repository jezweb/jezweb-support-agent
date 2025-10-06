<?php
/**
 * Chat Widget
 * Renders the support chat widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jezweb_Support_Chat_Widget {

    /**
     * Render the chat widget
     */
    public function render() {
        $site_id = get_option('jezweb_site_id', '');
        $agent_url = get_option('jezweb_agent_url', 'https://support.jezweb.workers.dev');

        ?>
        <div id="jezweb-chat-widget" style="
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999999;
        ">
            <button id="jezweb-chat-toggle" style="
                background: #0073aa;
                color: white;
                border: none;
                border-radius: 50%;
                width: 60px;
                height: 60px;
                cursor: pointer;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                font-size: 24px;
            " title="AI Support Assistant">
                💬
            </button>

            <div id="jezweb-chat-container" style="
                display: none;
                position: absolute;
                bottom: 70px;
                right: 0;
                width: 400px;
                height: 600px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                flex-direction: column;
            ">
                <div style="
                    background: #0073aa;
                    color: white;
                    padding: 15px;
                    border-radius: 10px 10px 0 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <h3 style="margin: 0; font-size: 16px;">Jezweb AI Support</h3>
                    <button id="jezweb-chat-close" style="
                        background: none;
                        border: none;
                        color: white;
                        font-size: 20px;
                        cursor: pointer;
                    ">×</button>
                </div>

                <div id="jezweb-chat-messages" style="
                    flex: 1;
                    overflow-y: auto;
                    padding: 20px;
                    background: #f5f5f5;
                ">
                    <div style="
                        background: white;
                        padding: 15px;
                        border-radius: 8px;
                        margin-bottom: 10px;
                    ">
                        <strong>AI Assistant:</strong>
                        <p style="margin: 5px 0 0 0;">
                            Hi! I'm your AI support assistant. I understand your WordPress site structure and Elementor pages.
                            <br><br>
                            Ask me things like:
                            <br>• "How do I change the hero heading on my homepage?"
                            <br>• "Where is the contact form located?"
                            <br>• "What plugins are active?"
                        </p>
                    </div>
                </div>

                <div style="
                    padding: 15px;
                    border-top: 1px solid #ddd;
                    background: white;
                    border-radius: 0 0 10px 10px;
                ">
                    <div style="display: flex; gap: 10px;">
                        <input
                            type="text"
                            id="jezweb-chat-input"
                            placeholder="Ask a question..."
                            style="
                                flex: 1;
                                padding: 10px;
                                border: 1px solid #ddd;
                                border-radius: 5px;
                            "
                        />
                        <button id="jezweb-chat-send" style="
                            background: #0073aa;
                            color: white;
                            border: none;
                            border-radius: 5px;
                            padding: 10px 20px;
                            cursor: pointer;
                        ">Send</button>
                    </div>
                    <?php if (empty($site_id)): ?>
                    <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
                        ⚠️ Configure Site ID in <a href="<?php echo admin_url('options-general.php?page=jezweb-support'); ?>">settings</a> to enable AI features
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
        (function($) {
            $(document).ready(function() {
                const $toggle = $('#jezweb-chat-toggle');
                const $container = $('#jezweb-chat-container');
                const $close = $('#jezweb-chat-close');
                const $input = $('#jezweb-chat-input');
                const $send = $('#jezweb-chat-send');
                const $messages = $('#jezweb-chat-messages');

                $toggle.on('click', function() {
                    $container.css('display', 'flex');
                    $input.focus();
                });

                $close.on('click', function() {
                    $container.hide();
                });

                function sendMessage() {
                    const message = $input.val().trim();
                    if (!message) return;

                    // Add user message
                    $messages.append(`
                        <div style="
                            background: #0073aa;
                            color: white;
                            padding: 10px 15px;
                            border-radius: 8px;
                            margin-bottom: 10px;
                            margin-left: 40px;
                            text-align: right;
                        ">
                            ${message}
                        </div>
                    `);

                    $input.val('');
                    $messages.scrollTop($messages[0].scrollHeight);

                    // Simulate AI response (placeholder - will connect to Cloudflare Agent later)
                    setTimeout(() => {
                        $messages.append(`
                            <div style="
                                background: white;
                                padding: 15px;
                                border-radius: 8px;
                                margin-bottom: 10px;
                            ">
                                <strong>AI Assistant:</strong>
                                <p style="margin: 5px 0 0 0;">
                                    I received your question: "${message}"
                                    <br><br>
                                    <em>(AI agent integration coming soon! For now, you can test the REST API endpoints at:
                                    <a href="<?php echo rest_url('jezweb/v1/site-knowledge'); ?>" target="_blank">Site Knowledge</a>)</em>
                                </p>
                            </div>
                        `);
                        $messages.scrollTop($messages[0].scrollHeight);
                    }, 500);
                }

                $send.on('click', sendMessage);
                $input.on('keypress', function(e) {
                    if (e.which === 13) {
                        sendMessage();
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}
