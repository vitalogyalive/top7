<?php
namespace Top7\Display;

/**
 * Modern table component using Tailwind CSS
 */
class Table {

    /**
     * Start a responsive table wrapper
     */
    public static function start(string $class = ''): void {
        ?>
        <div class="overflow-x-auto rounded-lg border border-gray-200 <?= htmlspecialchars($class) ?>">
            <table class="min-w-full divide-y divide-gray-200">
        <?php
    }

    /**
     * End table wrapper
     */
    public static function end(): void {
        ?>
            </table>
        </div>
        <?php
    }

    /**
     * Render table header
     */
    public static function header(array $columns): void {
        ?>
        <thead class="bg-gray-50">
            <tr>
                <?php foreach ($columns as $col): ?>
                    <?php
                    $align = $col['align'] ?? 'left';
                    $width = isset($col['width']) ? "width: {$col['width']}" : '';
                    $alignClass = match($align) {
                        'center' => 'text-center',
                        'right' => 'text-right',
                        default => 'text-left'
                    };
                    ?>
                    <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider <?= $alignClass ?>" style="<?= $width ?>">
                        <?= htmlspecialchars($col['label'] ?? '') ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <?php
    }

    /**
     * Start table body
     */
    public static function bodyStart(): void {
        ?>
        <tbody class="bg-white divide-y divide-gray-200">
        <?php
    }

    /**
     * End table body
     */
    public static function bodyEnd(): void {
        ?>
        </tbody>
        <?php
    }

    /**
     * Render a table row
     */
    public static function row(array $cells, array $options = []): void {
        $highlight = $options['highlight'] ?? false;
        $clickable = $options['clickable'] ?? false;
        $rowClass = $highlight ? 'bg-primary-50' : '';
        $rowClass .= $clickable ? ' cursor-pointer hover:bg-gray-100' : '';
        ?>
        <tr class="<?= $rowClass ?>">
            <?php foreach ($cells as $cell): ?>
                <?php
                $value = is_array($cell) ? ($cell['value'] ?? '') : $cell;
                $align = is_array($cell) ? ($cell['align'] ?? 'left') : 'left';
                $type = is_array($cell) ? ($cell['type'] ?? 'text') : 'text';
                $class = is_array($cell) ? ($cell['class'] ?? '') : '';

                $alignClass = match($align) {
                    'center' => 'text-center',
                    'right' => 'text-right',
                    default => 'text-left'
                };
                ?>
                <td class="px-4 py-3 whitespace-nowrap text-sm <?= $alignClass ?> <?= htmlspecialchars($class) ?>">
                    <?php self::renderCellValue($value, $type); ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <?php
    }

    /**
     * Render cell value based on type
     */
    private static function renderCellValue($value, string $type): void {
        switch ($type) {
            case 'rank':
                self::renderRank($value);
                break;
            case 'evolution':
                self::renderEvolution($value);
                break;
            case 'badge':
                echo '<span class="badge badge-primary">' . htmlspecialchars($value) . '</span>';
                break;
            case 'player':
                echo '<span class="font-medium text-gray-900">' . htmlspecialchars($value) . '</span>';
                break;
            case 'points':
                echo '<span class="font-semibold text-primary-600">' . htmlspecialchars($value) . '</span>';
                break;
            default:
                echo htmlspecialchars($value);
        }
    }

    /**
     * Render rank with medal icons
     */
    private static function renderRank($rank): void {
        $medal = match((int)$rank) {
            1 => '<span class="text-lg mr-1">🥇</span>',
            2 => '<span class="text-lg mr-1">🥈</span>',
            3 => '<span class="text-lg mr-1">🥉</span>',
            default => ''
        };
        echo $medal . '<span class="font-bold text-gray-900">' . htmlspecialchars($rank) . '</span>';
    }

    /**
     * Render evolution arrow
     */
    private static function renderEvolution($evo): void {
        if ($evo > 0) {
            echo '<span class="inline-flex items-center text-green-600">';
            echo '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>';
            echo '</span>';
        } elseif ($evo < 0) {
            echo '<span class="inline-flex items-center text-red-600">';
            echo '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>';
            echo '</span>';
        } else {
            echo '<span class="inline-flex items-center text-gray-400">';
            echo '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/></svg>';
            echo '</span>';
        }
    }

    /**
     * Render a ranking table (specialized for Top7 rankings)
     */
    public static function ranking(array $ranks, int $currentDay, string $title = ''): void {
        ?>
        <div class="card">
            <?php if ($title): ?>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($title) ?></h3>
                <div class="flex items-center space-x-2">
                    <form action="rank7.php" method="post" class="inline">
                        <input type="hidden" name="prev" value="1">
                        <button type="submit" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                    </form>
                    <span class="px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm font-medium">
                        J<?= $currentDay ?>
                    </span>
                    <form action="rank7.php" method="post" class="inline">
                        <input type="hidden" name="next" value="1">
                        <button type="submit" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php
            self::start();
            self::header([
                ['label' => '#', 'align' => 'center', 'width' => '50px'],
                ['label' => '', 'align' => 'center', 'width' => '40px'],
                ['label' => 'Joueur', 'align' => 'left'],
                ['label' => 'Points', 'align' => 'center'],
                ['label' => 'VE', 'align' => 'center'],
                ['label' => 'NE', 'align' => 'center'],
                ['label' => 'PC', 'align' => 'center'],
                ['label' => 'Fun', 'align' => 'center']
            ]);
            self::bodyStart();

            $rank = 1;
            foreach ($ranks as $player) {
                self::row([
                    ['value' => $rank, 'type' => 'rank', 'align' => 'center'],
                    ['value' => $player['evo'] ?? 0, 'type' => 'evolution', 'align' => 'center'],
                    ['value' => $player['pseudo'] ?? '', 'type' => 'player'],
                    ['value' => $player['pt'] ?? 0, 'type' => 'points', 'align' => 'center'],
                    ['value' => $player['ve'] ?? 0, 'align' => 'center'],
                    ['value' => $player['ne'] ?? 0, 'align' => 'center'],
                    ['value' => $player['pc'] ?? 0, 'align' => 'center'],
                    ['value' => $player['fun'] ?? 0, 'align' => 'center', 'class' => 'text-secondary-600 font-medium']
                ], ['highlight' => $rank <= 3]);
                $rank++;
            }

            self::bodyEnd();
            self::end();
            ?>
        </div>
        <?php
    }
}
