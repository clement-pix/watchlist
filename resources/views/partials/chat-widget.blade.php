<aside class="community-chat" data-chat data-messages-url="{{ route('chat.messages') }}" data-send-url="{{ route('chat.store') }}">
    <section class="chat-panel" data-chat-panel hidden aria-label="Salon des membres">
        <header><div><span class="chat-live-dot"></span><strong>Le Salon</strong><small>Discutez films et séries</small></div><button type="button" data-chat-close aria-label="Fermer le chat">×</button></header>
        <div class="chat-rules">Respect, bienveillance et aucun spoiler sans avertissement.</div>
        <div class="chat-messages" data-chat-messages><p class="chat-empty">Chargement des échanges…</p></div>
        <form class="chat-form" data-chat-form>
            <div class="chat-reply-preview" data-chat-reply hidden><div><small>Réponse à <strong data-chat-reply-author></strong></small><p data-chat-reply-text></p></div><button type="button" data-chat-reply-cancel aria-label="Annuler la réponse">×</button></div>
            <input type="hidden" name="reply_to_id" data-chat-reply-id>
            <label class="sr-only" for="chat-message">Votre message</label>
            <textarea id="chat-message" name="message" maxlength="500" rows="2" placeholder="Écrire au salon…" required></textarea>
            <div><small data-chat-status></small><button class="button button-small" type="submit">Envoyer</button></div>
        </form>
    </section>
    <div class="chat-context-menu" data-chat-context hidden role="menu" aria-label="Actions du message"></div>
    <button class="chat-toggle" type="button" data-chat-toggle aria-expanded="false" aria-label="Ouvrir le salon des membres"><span>✦</span><strong>Le Salon</strong></button>
</aside>
