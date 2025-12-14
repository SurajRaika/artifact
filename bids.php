<?php

// session_start();

// Assuming $link is your mysqli connection



require_once __DIR__ . "/app/config.php";
require_once __DIR__ . "/app/model/Artifact.php";

$artifact_model = new Artifact($link);

// you can get img of any artifact using http://localhost:8000/api/artifact_image.php?artifact_id=15&slide=1

$user_id = $_SESSION['id'] ?? null; // Corrected to use $_SESSION['id']
$my_artifacts = [];
if ($user_id) {
    // This call now includes 'verified' from the Artifact model
    $my_artifacts = $artifact_model->get_artifacts_by_seller_id($user_id);
}

























// Helper function to determine verification status badge
function getVerificationBadge($verified)
{
    if ((int)$verified === 1) {
        return '<span class="text-green-600 font-bold ml-1" title="Verified"><i data-lucide="check-circle" class="w-4 h-4 inline-block"></i> Verified</span>';
    }
    return '<span class="text-yellow-600 font-bold ml-1" title="Pending Verification"><i data-lucide="alert-triangle" class="w-4 h-4 inline-block"></i> Pending</span>';
}

// --- NEW CODE: Flash Message Retrieval ---
// This is the core logic to retrieve and clear the temporary session message.
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // Clear the message immediately after retrieval
}
// --- END NEW CODE ---
?>

<?php $page = "My Portfolio";
ob_start(); ?>

<div id="my-bids-view" class="view-content p-4 md:p-8 max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold uppercase tracking-tight brutalist-border-b pb-2 mb-4">My Portfolio</h1>

    <?php if (!empty($my_artifacts)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($my_artifacts as $artifact):
                $artifact_id = $artifact['id'];
                // Use 'is_private' for visibility status, default to 0 (public) if not set.
                $is_private = $artifact['visibility'] ?? 0;
                // Use 'verified', default to 0 (pending) if not set.
                $verified = $artifact['verified'] ?? 0;

                $visibility_text = $is_private ? 'Private' : 'Public';
                $toggle_visibility_text = $is_private ? 'Make Public' : 'Make Private';
                $verification_badge = getVerificationBadge($verified);
            ?>
                
                <div id="artifact-card-<?php echo $artifact_id; ?>" class="bg-white brutalist-shadow-sm hover:translate-x-1 transition-transform duration-200 p-4 brutalist-border relative">

                    <div>
                        <span class="text-xs font-mono uppercase text-gray-500 flex justify-between items-center">
                            <span>LOT <?php echo $artifact_id; ?> </span>
                            <?php echo $verification_badge; ?>
                        </span>

                        <h3 class="font-bold text-lg uppercase leading-tight mt-1 mb-3"><?php echo htmlspecialchars($artifact['name']); ?></h3>

                        <div class="w-full h-32 border-2 border-dashed border-gray-300 bg-gray-100 mb-3 flex items-center justify-center">
                            <img src="/api/artifact_image.php?artifact_id=<?php echo $artifact_id; ?>&slide=1" alt="<?php echo htmlspecialchars($artifact['name']); ?>" class="w-full h-full object-cover  ">
                        </div>

                        <p class="text-xs text-gray-600 mb-3 truncate"><?php echo htmlspecialchars($artifact['description']); ?></p>
                    </div>

                    <div class="flex justify-between items-center border-t border-gray-200 pt-2">
                        <button 
                        <?php echo $artifact_id; ?>
                            onclick="selectArtifact(<?php echo $artifact_id; ?>)" 
                            class="text-sm font-bold uppercase text-black hover:text-gray-700 transition-colors duration-150">
                            <!--  -->
                            View Detail
                        </button>

                        <div class="flex space-x-3">
                            <button 
                                title="<?php echo $toggle_visibility_text; ?>"
                                onclick="changeVisibility(<?php echo $artifact_id; ?>, <?php echo $is_private ? 0 : 1; ?>)" 
                                class="p-1 text-gray-500 hover:text-black transition-colors duration-150"
                            >
                                <i data-lucide="<?php echo $is_private ? 'eye-off' : 'eye'; ?>" class="w-5 h-5"></i>
                            </button>
                            
                            <button 
                                title="Delete Artifact"
                                onclick="deleteArtifact(<?php echo $artifact_id; ?>)" 
                                class="p-1 text-red-600 hover:text-red-800 transition-colors duration-150"
                            >
                                <i data-lucide="trash" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                </div>


            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div id="no-bids-state" class="text-center p-12 brutalist-border brutalist-shadow bg-white mt-8">
            <i data-lucide="folder-open" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
            <h3 class="font-bold text-lg uppercase mb-2">No Artifacts Found</h3>
            <p class="text-sm text-gray-500 font-mono">You haven't created any artifacts yet.</p>
        </div>
    <?php endif; ?>

</div>

<script>
    // Placeholder showToast function (define this if it's not already global)
    function showToast(message) {
        // In a real application, this would display a temporary notification box
        console.log('TOAST: ' + message);
        // alert(message); // Use this for simple debugging if no proper toast is available
    }
    
    // --- Display Flash Message on Load (NEW CODE) ---
    <?php if ($flash_message): ?>
        // Call showToast with the message retrieved from the session before it was cleared
        showToast("<?php echo addslashes($flash_message); ?>");
    <?php endif; ?>
    // --- END NEW CODE ---




// --- EDITED changeVisibility FUNCTION ---
function changeVisibility(artifactId, newVisibility) {
        showToast('Attempting to change visibility for Artifact ' + artifactId + ' to ' + (newVisibility ? 'Private' : 'Public') + '...');

        const formData = new FormData();
        formData.append("artifact_id", artifactId);
        formData.append("visibility", newVisibility);

        fetch("/api/update_artifact_visibility.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // If the API call succeeds (and the API successfully set the session flash message)
                console.log("Updated! Reloading page to reflect new state and show success message.");
                window.location.reload(); // **RELOAD THE PAGE**
            } else {
                console.error("Failed:", data.message);
                showToast("ERROR: Failed to update visibility: " + data.message);
            }
        })
        .catch(err => {
            console.error("Error:", err);
            showToast("ERROR: Network or server error.");
        });
    }
// --- END EDITED FUNCTION ---





    // --- NEW deleteArtifact FUNCTION ---
function deleteArtifact(artifactId) {
    // 1. Confirmation Dialog for safety
    if (!confirm("Are you sure you want to permanently delete this artifact? This action cannot be undone.")) {
        return; // Stop if the user cancels
    }

    showToast('Attempting to delete Artifact ' + artifactId + '...');

    const formData = new FormData();
    formData.append("artifact_id", artifactId);

    fetch("/api/delete_artifact.php", {
        method: "POST",
        body: formData
    })
    .then(res => {
        // Check for HTTP errors first
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            console.log("Deleted successfully! Showing success message and reloading.");
            
            // Assuming you want to show a specific success toast before reload
            showToast("SUCCESS: Artifact " + artifactId + " deleted.");

            // Optionally, you might want a small delay before reloading 
            // to ensure the user sees the success toast.
            setTimeout(() => {
                window.location.reload(); // **RELOAD THE PAGE** to reflect the deletion
            }, 1000); 

        } else {
            console.error("Deletion Failed:", data.message);
            showToast("ERROR: Failed to delete artifact: " + data.message);
        }
    })
    .catch(err => {
        console.error("Error during deletion:", err);
        showToast("ERROR: Network or server error during deletion.");
    });
}
</script>

<?php $slot = ob_get_clean();
include 'app/components/layouts/base.php';
?>