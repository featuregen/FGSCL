<?php
/**
 * Module Service
 * Manages feature module availability per school.
 * 
 * Module resolution order:
 * 1. Core modules (is_core = 1) → always enabled
 * 2. school_modules table → per-school override by Super Admin
 * 3. Plan's features JSON → default modules for the plan
 */

class ModuleService
{
    /** @var array Cache of enabled modules per school */
    private static array $cache = [];

    /**
     * Get all available modules (master list)
     */
    public static function getAllModules(): array
    {
        return Database::fetchAll(
            "SELECT * FROM modules WHERE is_active = 1 ORDER BY sort_order"
        );
    }

    /**
     * Get modules grouped by category
     */
    public static function getModulesByCategory(): array
    {
        $modules = self::getAllModules();
        $grouped = [];
        foreach ($modules as $mod) {
            $grouped[$mod['category']][] = $mod;
        }
        return $grouped;
    }

    /**
     * Get enabled module slugs for a school
     * Returns array of module slugs that are active
     */
    public static function getEnabledModules(int $schoolId): array
    {
        if (isset(self::$cache[$schoolId])) {
            return self::$cache[$schoolId];
        }

        // 1. Get all core modules (always enabled)
        $coreModules = Database::fetchAll(
            "SELECT slug FROM modules WHERE is_core = 1 AND is_active = 1"
        );
        $enabled = array_column($coreModules, 'slug');

        // 2. Check if school has specific module overrides
        $overrides = Database::fetchAll(
            "SELECT m.slug, sm.is_enabled 
             FROM school_modules sm 
             JOIN modules m ON sm.module_id = m.id 
             WHERE sm.school_id = ?",
            [$schoolId]
        );

        if (!empty($overrides)) {
            // Use school-specific overrides
            foreach ($overrides as $row) {
                if ($row['is_enabled'] && !in_array($row['slug'], $enabled)) {
                    $enabled[] = $row['slug'];
                }
            }
        } else {
            // 3. Fallback to plan's default features
            $planFeatures = Database::fetch(
                "SELECT p.features 
                 FROM subscriptions s 
                 JOIN plans p ON s.plan_id = p.id 
                 WHERE s.school_id = ? AND s.status = 'active' 
                 ORDER BY s.created_at DESC LIMIT 1",
                [$schoolId]
            );

            if ($planFeatures && $planFeatures['features']) {
                $features = json_decode($planFeatures['features'], true) ?? [];
                $enabled = array_unique(array_merge($enabled, $features));
            }
        }

        self::$cache[$schoolId] = $enabled;
        return $enabled;
    }

    /**
     * Check if a specific module is enabled for a school
     */
    public static function isEnabled(int $schoolId, string $moduleSlug): bool
    {
        // Super Admin always has access to everything
        if (Session::userRole() === ROLE_SUPER_ADMIN) {
            return true;
        }

        return in_array($moduleSlug, self::getEnabledModules($schoolId));
    }

    /**
     * Check module from current session
     */
    public static function hasModule(string $moduleSlug): bool
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            // No school context (Super Admin) → allow all
            return true;
        }
        return self::isEnabled($schoolId, $moduleSlug);
    }

    /**
     * Set modules for a school (Super Admin action)
     * 
     * @param int   $schoolId
     * @param array $moduleSlugs Array of module slugs to enable
     * @param int   $enabledBy   Super Admin user ID
     */
    public static function setSchoolModules(int $schoolId, array $moduleSlugs, int $enabledBy): void
    {
        // Clear existing overrides
        Database::pdo()->prepare("DELETE FROM school_modules WHERE school_id = ?")->execute([$schoolId]);

        // Get all module IDs
        $allModules = Database::fetchAll("SELECT id, slug, is_core FROM modules WHERE is_active = 1");
        
        foreach ($allModules as $mod) {
            // Core modules are always enabled
            $isEnabled = $mod['is_core'] ? 1 : (in_array($mod['slug'], $moduleSlugs) ? 1 : 0);

            Database::insert('school_modules', [
                'school_id'  => $schoolId,
                'module_id'  => $mod['id'],
                'is_enabled' => $isEnabled,
                'enabled_by' => $enabledBy,
                'enabled_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Clear cache
        unset(self::$cache[$schoolId]);
    }

    /**
     * Initialize modules for a new school based on plan features
     */
    public static function initFromPlan(int $schoolId, array $planFeatures, int $enabledBy): void
    {
        $allModules = Database::fetchAll("SELECT id, slug, is_core FROM modules WHERE is_active = 1");
        
        foreach ($allModules as $mod) {
            $isEnabled = $mod['is_core'] ? 1 : (in_array($mod['slug'], $planFeatures) ? 1 : 0);

            Database::insert('school_modules', [
                'school_id'  => $schoolId,
                'module_id'  => $mod['id'],
                'is_enabled' => $isEnabled,
                'enabled_by' => $enabledBy,
                'enabled_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Get module status for a school (for admin UI)
     * Returns all modules with is_enabled flag
     */
    public static function getSchoolModuleStatus(int $schoolId): array
    {
        $enabledSlugs = self::getEnabledModules($schoolId);
        $allModules = self::getAllModules();

        foreach ($allModules as &$mod) {
            $mod['is_enabled'] = $mod['is_core'] || in_array($mod['slug'], $enabledSlugs);
        }

        return $allModules;
    }

    /**
     * Get category labels for display
     */
    public static function getCategoryLabels(): array
    {
        return [
            'core'          => ['label' => 'Core',          'icon' => 'bi-shield-check', 'color' => '#4F46E5'],
            'academic'      => ['label' => 'Academic',      'icon' => 'bi-mortarboard',  'color' => '#059669'],
            'communication' => ['label' => 'Communication', 'icon' => 'bi-chat-dots',    'color' => '#0891B2'],
            'finance'       => ['label' => 'Finance',       'icon' => 'bi-currency-rupee','color' => '#D97706'],
            'resources'     => ['label' => 'Resources',     'icon' => 'bi-box-seam',     'color' => '#7C3AED'],
            'other'         => ['label' => 'Other',         'icon' => 'bi-grid',         'color' => '#6B7280'],
            'premium'       => ['label' => 'Premium',       'icon' => 'bi-star',         'color' => '#DC2626'],
        ];
    }
}
