<?php
namespace Top7\Display;

use Top7\Security\CsrfToken;

/**
 * Modern forum component using Tailwind CSS and Alpine.js
 */
class Forum {

    /**
     * Render the forum section with posts and form
     */
    public static function render(array $posts, array $session, int $maxPosts = 10): void {
        $player = $session['player'] ?? 0;
        $pseudo = $session['pseudo'] ?? '';
        ?>
        <div class="card mt-6" x-data="{ showForm: false }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                    </svg>
                    Forum
                </h3>
                <button
                    @click="showForm = !showForm"
                    class="btn btn-primary text-sm"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau message
                </button>
            </div>

            <!-- New Post Form -->
            <div
                x-show="showForm"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200"
                style="display: none;"
            >
                <form action="update_forum.php" method="post">
                    <?php if (class_exists('\Top7\Security\CsrfToken')): ?>
                        <?= CsrfToken::field() ?>
                    <?php endif; ?>
                    <input type="hidden" name="pseudo" value="<?= htmlspecialchars($pseudo) ?>">

                    <div class="mb-3">
                        <label for="forum_title" class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                        <input
                            type="text"
                            id="forum_title"
                            name="title"
                            required
                            maxlength="100"
                            class="input-field"
                            placeholder="Titre du message"
                        >
                    </div>

                    <div class="mb-3">
                        <label for="forum_message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea
                            id="forum_message"
                            name="message"
                            required
                            rows="3"
                            class="input-field"
                            placeholder="Votre message..."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2">
                        <button type="button" @click="showForm = false" class="btn btn-secondary text-sm">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-primary text-sm">
                            Publier
                        </button>
                    </div>
                </form>
            </div>

            <!-- Posts List -->
            <div class="space-y-4">
                <?php if (empty($posts)): ?>
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p>Aucun message pour le moment</p>
                    <button @click="showForm = true" class="mt-2 text-primary-600 hover:text-primary-700 font-medium">
                        Soyez le premier à poster !
                    </button>
                </div>
                <?php else: ?>
                    <?php
                    $count = 0;
                    foreach ($posts as $post):
                        if ($count >= $maxPosts) break;
                        $isOwner = ($player == ($post['player'] ?? 0));
                        $count++;
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow" x-data="{ editing: false }">
                        <!-- Post Header -->
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                    <span class="text-primary-600 font-semibold">
                                        <?= strtoupper(substr($post['pseudo'] ?? 'A', 0, 1)) ?>
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900"><?= htmlspecialchars($post['pseudo'] ?? 'Anonyme') ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($post['date'] ?? '') ?></p>
                                </div>
                            </div>

                            <?php if ($isOwner): ?>
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="editing = !editing"
                                    class="p-1 text-gray-400 hover:text-primary-600 transition-colors"
                                    title="Modifier"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <form action="delete_forum.php" method="post" class="inline" onsubmit="return confirm('Supprimer ce message ?');">
                                    <?php if (class_exists('\Top7\Security\CsrfToken')): ?>
                                        <?= CsrfToken::field() ?>
                                    <?php endif; ?>
                                    <input type="hidden" name="id" value="<?= (int)($post['id'] ?? 0) ?>">
                                    <button type="submit" class="p-1 text-gray-400 hover:text-red-600 transition-colors" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Post Content (View Mode) -->
                        <div x-show="!editing">
                            <h4 class="font-medium text-gray-900 mb-1"><?= htmlspecialchars($post['title'] ?? '') ?></h4>
                            <p class="text-gray-600 text-sm whitespace-pre-wrap"><?= htmlspecialchars($post['message'] ?? '') ?></p>
                        </div>

                        <!-- Post Content (Edit Mode) -->
                        <?php if ($isOwner): ?>
                        <div x-show="editing" style="display: none;">
                            <form action="edit_forum.php" method="post">
                                <?php if (class_exists('\Top7\Security\CsrfToken')): ?>
                                    <?= CsrfToken::field() ?>
                                <?php endif; ?>
                                <input type="hidden" name="id" value="<?= (int)($post['id'] ?? 0) ?>">

                                <div class="mb-2">
                                    <input
                                        type="text"
                                        name="title"
                                        value="<?= htmlspecialchars($post['title'] ?? '') ?>"
                                        class="input-field text-sm"
                                        required
                                    >
                                </div>
                                <div class="mb-2">
                                    <textarea
                                        name="message"
                                        rows="3"
                                        class="input-field text-sm"
                                        required
                                    ><?= htmlspecialchars($post['message'] ?? '') ?></textarea>
                                </div>
                                <div class="flex items-center justify-end space-x-2">
                                    <button type="button" @click="editing = false" class="btn btn-secondary text-xs py-1 px-3">
                                        Annuler
                                    </button>
                                    <button type="submit" class="btn btn-primary text-xs py-1 px-3">
                                        Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <?php if (count($posts) > $maxPosts): ?>
                    <div class="text-center py-2">
                        <a href="forum.php" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
                            Voir tous les messages (<?= count($posts) ?>)
                        </a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
