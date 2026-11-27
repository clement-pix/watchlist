import './bootstrap';

const toggle = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('[data-menu]');
const setMenu = (open) => {
    if (!toggle || !menu) return;
    toggle.setAttribute('aria-expanded', String(open));
    toggle.querySelector('.sr-only').textContent = open ? 'Fermer le menu' : 'Ouvrir le menu';
    menu.classList.toggle('is-open', open);
    document.body.classList.toggle('menu-open', open);
};
toggle?.addEventListener('click', () => setMenu(toggle.getAttribute('aria-expanded') !== 'true'));
menu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setMenu(false)));
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') setMenu(false); });
window.addEventListener('resize', () => { if (window.innerWidth > 760) setMenu(false); });
document.querySelectorAll('[data-current-year]').forEach((element) => { element.textContent = String(new Date().getFullYear()); });

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
const reportDialog = document.querySelector('[data-report-dialog]');
const openReport = (url, label) => {
    if (!reportDialog) return;
    reportDialog.querySelector('[data-report-form]').action = url;
    reportDialog.querySelector('[data-report-target]').textContent = label;
    reportDialog.showModal();
};
reportDialog?.querySelectorAll('[data-report-close]').forEach((button) => button.addEventListener('click', () => reportDialog.close()));
reportDialog?.addEventListener('click', (event) => { if (event.target === reportDialog) reportDialog.close(); });
document.querySelectorAll('[data-report-url]').forEach((button) => button.addEventListener('click', () => openReport(button.dataset.reportUrl, button.dataset.reportLabel)));
const chat = document.querySelector('[data-chat]');

if (chat) {
    const panel = chat.querySelector('[data-chat-panel]');
    const toggleChat = chat.querySelector('[data-chat-toggle]');
    const closeChat = chat.querySelector('[data-chat-close]');
    const messagesBox = chat.querySelector('[data-chat-messages]');
    const form = chat.querySelector('[data-chat-form]');
    const status = chat.querySelector('[data-chat-status]');
    const contextMenu = chat.querySelector('[data-chat-context]');
    const replyPreview = chat.querySelector('[data-chat-reply]');
    const replyId = chat.querySelector('[data-chat-reply-id]');
    const replyAuthor = chat.querySelector('[data-chat-reply-author]');
    const replyText = chat.querySelector('[data-chat-reply-text]');
    let timer;
    let unlockedReactions = [];

    const request = async (url, options = {}) => {
        const response = await fetch(url, { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, ...options });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'Une erreur est survenue.');
        return data;
    };
    const hideContext = () => { contextMenu.hidden = true; contextMenu.replaceChildren(); };
    const clearReply = () => { replyPreview.hidden = true; replyId.value = ''; };
    const startReply = (item) => {
        replyId.value = item.id; replyAuthor.textContent = item.author; replyText.textContent = item.message;
        replyPreview.hidden = false; hideContext(); form.querySelector('textarea').focus();
    };
    const actionButton = (label, action, className = '') => {
        const button = document.createElement('button'); button.type = 'button'; button.textContent = label;
        if (className) button.className = className;
        button.addEventListener('click', action); return button;
    };
    const moderate = async (url, method = 'POST', body = null) => {
        status.textContent = '';
        try { await request(url, { method, body }); hideContext(); await load(); }
        catch (error) { status.textContent = error.message; }
    };
    const showContext = (item, x, y) => {
        contextMenu.replaceChildren();
        const heading = document.createElement('small'); heading.textContent = `Actions · ${item.author}`; contextMenu.append(heading);
        contextMenu.append(actionButton('↩ Répondre', () => startReply(item)));

        if (unlockedReactions.length) {
            const label = document.createElement('small'); label.textContent = 'Réagir avec'; contextMenu.append(label);
            const reactions = document.createElement('div'); reactions.className = 'chat-context-reactions';
            unlockedReactions.forEach((reward) => reactions.append(actionButton(reward.emoji, async () => {
                await moderate(`/chat/messages/${item.id}/reactions/${reward.id}`);
            }, 'chat-context-emoji')));
            contextMenu.append(reactions);
        }
        if (item.can_delete) contextMenu.append(actionButton('Supprimer le message', async () => {
            if (!confirm('Supprimer ce message ?')) return;
            await moderate(item.delete_url, 'DELETE');
        }, 'is-danger'));
        if (!item.mine) contextMenu.append(actionButton('⚑ Signaler le message', () => { hideContext(); openReport(item.report_url, `Message de ${item.author}`); }));

        if (item.can_sanction) {
            const moderatorLabel = document.createElement('small'); moderatorLabel.textContent = 'Modération'; contextMenu.append(moderatorLabel);
            const muteRow = document.createElement('div'); muteRow.className = 'chat-context-mute';
            [['10 min', 10], ['1 h', 60], ['24 h', 1440], ['7 j', 10080]].forEach(([label, duration]) => muteRow.append(actionButton(label, async () => {
                const data = new FormData(); data.set('duration', duration); data.set('reason', 'Modération depuis le Salon');
                await moderate(item.mute_url, 'POST', data);
            })));
            contextMenu.append(muteRow);
            contextMenu.append(actionButton('Bannir définitivement', async () => {
                if (!confirm(`Bannir ${item.author} du Salon ?`)) return;
                await moderate(item.ban_url);
            }, 'is-danger'));
            if (item.is_suspended) contextMenu.append(actionButton('Rétablir l’accès', async () => moderate(item.unban_url, 'DELETE')));
        }

        contextMenu.hidden = false;
        const rect = contextMenu.getBoundingClientRect();
        contextMenu.style.left = `${Math.max(10, Math.min(x, window.innerWidth - rect.width - 10))}px`;
        contextMenu.style.top = `${Math.max(10, Math.min(y, window.innerHeight - rect.height - 10))}px`;
    };

    const render = (payload) => {
        const messages = payload.messages || [];
        unlockedReactions = payload.unlocked_reactions || [];
        hideContext(); messagesBox.replaceChildren();
        if (!messages.length) {
            const empty = document.createElement('p'); empty.className = 'chat-empty'; empty.textContent = 'Soyez le premier à lancer la discussion.'; messagesBox.append(empty); return;
        }
        messages.forEach((item) => {
            const article = document.createElement('article'); article.className = `chat-message${item.mine ? ' is-mine' : ''}`;
            article.dataset.messageId = item.id;
            article.addEventListener('contextmenu', (event) => { event.preventDefault(); showContext(item, event.clientX, event.clientY); });
            const meta = document.createElement('div'); meta.className = 'chat-message-meta';
            const author = document.createElement('strong'); author.textContent = `${item.emoji ? `${item.emoji} ` : ''}${item.author}`;
            const rank = document.createElement('span'); rank.textContent = `${item.rank || ''} · ${item.time || ''}`;
            const more = actionButton('•••', (event) => { event.stopPropagation(); const rect = event.currentTarget.getBoundingClientRect(); showContext(item, rect.right, rect.bottom); }, 'chat-more');
            more.setAttribute('aria-label', `Actions pour le message de ${item.author}`);
            meta.append(author, rank, more); article.append(meta);
            if (item.reply) {
                const quote = document.createElement('button'); quote.type = 'button'; quote.className = 'chat-reply-quote';
                const quoteAuthor = document.createElement('strong'); quoteAuthor.textContent = `↩ ${item.reply.author}`;
                const quoteBody = document.createElement('span'); quoteBody.textContent = item.reply.message;
                quote.append(quoteAuthor, quoteBody); article.append(quote);
            }
            const body = document.createElement('p'); body.textContent = item.message; article.append(body);
            if (item.reactions?.length) {
                const reactionBar = document.createElement('div'); reactionBar.className = 'chat-reactions';
                item.reactions.forEach((reaction) => {
                    const chip = actionButton(`${reaction.emoji} ${reaction.count}`, async () => moderate(`/chat/messages/${item.id}/reactions/${reaction.reward_id}`), reaction.mine ? 'is-mine' : '');
                    chip.title = reaction.name || 'Réaction'; reactionBar.append(chip);
                });
                article.append(reactionBar);
            }
            messagesBox.append(article);
        });
        messagesBox.scrollTop = messagesBox.scrollHeight;
    };

    const load = async () => {
        try { render(await request(chat.dataset.messagesUrl)); }
        catch (error) { status.textContent = error.message; }
    };
    const setOpen = async (open) => {
        panel.hidden = !open; toggleChat.setAttribute('aria-expanded', String(open));
        if (open) { await load(); timer = window.setInterval(load, 8000); form.querySelector('textarea').focus(); }
        else { window.clearInterval(timer); hideContext(); }
    };
    toggleChat.addEventListener('click', () => setOpen(panel.hidden));
    closeChat.addEventListener('click', () => setOpen(false));
    chat.querySelector('[data-chat-reply-cancel]').addEventListener('click', clearReply);
    form.addEventListener('submit', async (event) => {
        event.preventDefault(); status.textContent = '';
        try { await request(chat.dataset.sendUrl, { method: 'POST', body: new FormData(form) }); form.reset(); clearReply(); await load(); }
        catch (error) { status.textContent = error.message; }
    });
    document.addEventListener('click', (event) => { if (!contextMenu.hidden && !contextMenu.contains(event.target)) hideContext(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') { hideContext(); clearReply(); } });
}

document.querySelectorAll('[data-admin-delete-chat]').forEach((button) => button.addEventListener('click', async () => {
    if (!confirm('Supprimer ce message du Salon ?')) return;
    const response = await fetch(button.dataset.adminDeleteChat, { method: 'DELETE', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } });
    if (response.ok) button.closest('article')?.remove();
}));
