<dialog class="report-dialog" data-report-dialog>
    <form method="POST" data-report-form>
        @csrf
        <header><div><span class="settings-icon">!</span><div><strong>Signaler un contenu</strong><small data-report-target></small></div></div><button type="button" data-report-close aria-label="Fermer">×</button></header>
        <p>Le signalement sera examiné par l’équipe de modération. Le contenu est conservé comme preuve, même s’il est ensuite supprimé.</p>
        <div class="field"><label for="report-reason">Motif</label><select id="report-reason" name="reason" required><option value="">Choisir un motif</option><option value="insult">Insulte ou propos haineux</option><option value="harassment">Harcèlement</option><option value="spam">Spam ou publicité</option><option value="spoiler">Spoiler non signalé</option><option value="illegal">Contenu illégal</option><option value="other">Autre raison</option></select></div>
        <div class="field"><label for="report-details">Précisions <span class="optional">facultatif</span></label><textarea id="report-details" name="details" maxlength="1000" rows="4" placeholder="Expliquez brièvement le problème…"></textarea></div>
        <div class="report-dialog-actions"><button class="button button-secondary" type="button" data-report-close>Annuler</button><button class="button" type="submit">Envoyer le signalement</button></div>
    </form>
</dialog>
