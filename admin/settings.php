<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

global $db;

// Get all settings grouped by their group
$settings = [];
$result = $db->query("SELECT * FROM system_settings ORDER BY setting_group, id");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_group']][] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $db->autocommit(false);
        
        foreach ($_POST['settings'] as $key => $value) {
            // For checkbox values that weren't checked
            $value = isset($value) ? $value : '0';
            
            $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
        
        $db->autocommit(false);
        $_SESSION['success_message'] = "Settings updated successfully!";
    } catch (Exception $e) {
        $db->commit();
        $_SESSION['error_message'] = "Error updating settings: " . $e->getMessage();
    }
    
    header("Location: settings.php");
    exit();
}

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="dashboard-header">
        <h1 class="h3 mb-0">System Settings</h1>
        <p class="text-muted mb-0">Configure system preferences and options</p>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                            <?php foreach ($settings as $group => $groupSettings): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $group === 'general' ? 'active' : ''; ?>" 
                                       id="<?php echo $group; ?>-tab" 
                                       data-toggle="tab" 
                                       href="#<?php echo $group; ?>" 
                                       role="tab">
                                        <?php echo ucfirst($group); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="settingsTabsContent">
                            <?php foreach ($settings as $group => $groupSettings): ?>
                                <div class="tab-pane fade <?php echo $group === 'general' ? 'show active' : ''; ?>" 
                                     id="<?php echo $group; ?>" 
                                     role="tabpanel">
                                    <?php foreach ($groupSettings as $setting): ?>
                                        <div class="form-group mb-4">
                                            <label for="setting_<?php echo $setting['setting_key']; ?>">
                                                <?php echo $setting['display_name']; ?>
                                            </label>
                                            
                                            <?php if ($setting['setting_type'] === 'text' || 
                                                      $setting['setting_type'] === 'email' || 
                                                      $setting['setting_type'] === 'password' || 
                                                      $setting['setting_type'] === 'number'): ?>
                                                <input type="<?php echo $setting['setting_type']; ?>" 
                                                       class="form-control" 
                                                       id="setting_<?php echo $setting['setting_key']; ?>" 
                                                       name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                       value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                                       <?php echo $setting['setting_type'] === 'number' ? 'min="0"' : ''; ?>>
                                            
                                            <?php elseif ($setting['setting_type'] === 'textarea'): ?>
                                                <textarea class="form-control" 
                                                          id="setting_<?php echo $setting['setting_key']; ?>" 
                                                          name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                          rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                            
                                            <?php elseif ($setting['setting_type'] === 'select'): ?>
                                                <?php $options = json_decode($setting['options'] ?? '[]', true); ?>
                                                <select class="form-control" 
                                                        id="setting_<?php echo $setting['setting_key']; ?>" 
                                                        name="settings[<?php echo $setting['setting_key']; ?>]">
                                                    <?php foreach ($options as $option): ?>
                                                        <option value="<?php echo htmlspecialchars($option); ?>" 
                                                            <?php echo $option === $setting['setting_value'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            
                                            <?php elseif ($setting['setting_type'] === 'checkbox'): ?>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" 
                                                           class="custom-control-input" 
                                                           id="setting_<?php echo $setting['setting_key']; ?>" 
                                                           name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                           value="1"
                                                           <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="setting_<?php echo $setting['setting_key']; ?>">
                                                        <?php echo $setting['setting_value'] == '1' ? 'Enabled' : 'Disabled'; ?>
                                                    </label>
                                                </div>
                                            
                                            <?php elseif ($setting['setting_type'] === 'radio'): ?>
                                                <?php $options = json_decode($setting['options'] ?? '[]', true); ?>
                                                <div class="form-group">
                                                    <?php foreach ($options as $option): ?>
                                                        <div class="custom-control custom-radio">
                                                            <input type="radio" 
                                                                   id="setting_<?php echo $setting['setting_key']; ?>_<?php echo htmlspecialchars($option); ?>" 
                                                                   name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                                   class="custom-control-input" 
                                                                   value="<?php echo htmlspecialchars($option); ?>"
                                                                   <?php echo $option === $setting['setting_value'] ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label" for="setting_<?php echo $setting['setting_key']; ?>_<?php echo htmlspecialchars($option); ?>">
                                                                <?php echo htmlspecialchars($option); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($setting['setting_key'] === 'smtp_password' && !empty($setting['setting_value'])): ?>
                                                <small class="form-text text-muted">Password is set (enter a new one to change it)</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Save Settings
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Settings Information</h6>
                    </div>
                    <div class="card-body">
                        <p>Use this panel to configure system-wide settings that control how your application behaves.</p>
                        <p class="mb-0"><strong>Note:</strong> Some changes may require a system restart to take effect.</p>
                    </div>
                </div>
                
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Danger Zone</h6>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-danger btn-block" data-toggle="modal" data-target="#clearCacheModal">
                            <i class="fas fa-broom mr-2"></i>Clear System Cache
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-block mt-2" data-toggle="modal" data-target="#resetSettingsModal">
                            <i class="fas fa-undo mr-2"></i>Reset to Defaults
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Clear Cache Modal -->
<div class="modal fade" id="clearCacheModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear System Cache</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to clear all system cache? This may temporarily slow down the system while cache rebuilds.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="clear_cache.php" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Clear Cache</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reset Settings Modal -->
<div class="modal fade" id="resetSettingsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Settings to Defaults</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Warning! This will reset all system settings to their default values. This action cannot be undone.</p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Some features may stop working until you reconfigure them.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="reset_settings.php" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Reset Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>