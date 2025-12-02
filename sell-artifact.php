<?php $page = "Sell Artifact";
ob_start(); ?>

<div id="sell-artifact-view" class="view-content p-4 md:p-8 max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold uppercase tracking-tight brutalist-border-b pb-2 mb-4">Sell Artifact for Appraisal</h1>

    <div id="appraisal-form-container" class="max-w-2xl mx-auto p-6 brutalist-border brutalist-shadow bg-white mt-8">
        <h2 class="text-xl font-bold uppercase mb-4">Submit Item Details</h2>
        <p class="text-sm text-gray-700 mb-6 font-mono">
            Please provide high-resolution images and detailed provenance for an accurate appraisal.
        </p>

        <div class="space-y-4">
            <div>
                <label for="artifact-title" class="block text-sm font-bold uppercase mb-1">Artifact Title</label>
                <input type="text" id="artifact-title" placeholder="e.g., Ming Dynasty Porcelain Vase"
                    class="w-full px-3 py-2 border-2 border-black font-mono focus:outline-none">
            </div>

            <div>
                <label for="artifact-description" class="block text-sm font-bold uppercase mb-1">Description & History</label>
                <textarea id="artifact-description" rows="4" placeholder="Brief history, condition, and any known provenance."
                    class="w-full px-3 py-2 border-2 border-black font-mono focus:outline-none"></textarea>
            </div>

            <div class="p-3 brutalist-border brutalist-shadow-sm bg-gray-50">
                <div class="flex items-center justify-between">
                    <label for="is-private-sale" class="text-sm font-bold uppercase mr-4">Private Sale (Requires Code)</label>
                    <div class="relative inline-block w-10 mr-2 select-none transition duration-200 ease-in">
                        <input type="checkbox" id="is-private-sale" name="is-private-sale"
                            class="toggle-checkbox absolute block w-6 h-6 mt-[1px] rounded-full bg-white border-black/50 border-2 appearance-none cursor-pointer"
                            onchange="toggleSecretCodeField()">
                        <label for="is-private-sale"
                            class="toggle-label block overflow-hidden h-6 rounded-full border-black/50  border-2 bg-gray-300 cursor-pointer"></label>
                    </div>
                </div>

                <div id="secret-code-field" class="mt-3 hidden space-y-3">
                    <div>
                        <label for="private-sale-code" class="block text-xs font-bold uppercase mb-1 text-gray-700">Secret Access Code</label>
                        <input type="password" id="private-sale-code" placeholder="Enter 6-digit code for private viewing"
                            class="w-full px-3 py-2 border-2 border-black font-mono focus:outline-none text-sm"
                            maxlength="6">
                    </div>
                    <div>
                        <label for="confirm-private-sale-code" class="block text-xs font-bold uppercase mb-1 text-gray-700">Confirm Secret Code</label>
                        <input type="password" id="confirm-private-sale-code" placeholder="Re-enter 6-digit code"
                            class="w-full px-3 py-2 border-2 border-black font-mono focus:outline-none text-sm"
                            maxlength="6">
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold uppercase mb-1">Upload Images (Max 5)</label>
                <div id="drop-area" class="brutalist-border border-2 border-dashed border-gray-400 p-6 text-center bg-gray-50 cursor-pointer"
                    onclick="document.getElementById('file-input').click()">
                    <i data-lucide="upload-cloud" class="w-6 h-6 text-gray-600 mx-auto mb-2"></i>
                    <p class="text-sm font-bold uppercase text-gray-700">Drag & Drop or Click to Upload</p>
                    <p class="text-xs text-gray-500 font-mono">Only image files are accepted.</p>
                    <input type="file" id="file-input" multiple accept="image/*" class="hidden"
                        onchange="handleFiles(this.files)">
                </div>

                <div id="file-list" class="mt-3 grid grid-cols-2 gap-2 md:grid-cols-3">
                    </div>
                <div id="file-list-placeholder" class="mt-2 text-xs text-gray-500 font-mono">No files selected.</div>
            </div>

            <button id="submit-btn" onclick="submitAppraisal()"
                class="w-full bg-black text-white py-3 font-bold uppercase tracking-widest hover:bg-red-600 transition-all brutalist-shadow brutalist-active border-2 border-black mt-4 disabled:bg-gray-400 disabled:cursor-not-allowed">
                Submit for Appraisal
            </button>
        </div>
    </div>

    <div id="appraisal-status-container" class="hidden max-w-2xl mx-auto p-6 brutalist-border brutalist-shadow bg-gray-800 text-white mt-8">
        <i data-lucide="hourglass" class="w-8 h-8 text-yellow-400 mb-3"></i>
        <h2 class="text-xl font-bold uppercase mb-4 text-yellow-400">Appraisal in Progress</h2>
        <p class="text-sm font-mono mb-4">
            Thank you for your submission. Your artifact is currently under review by our experts.
            Appraisal time is typically 24-48 hours.
        </p>

        <div class="p-3 brutalist-border bg-black text-xs font-mono mb-6">
            <p><strong>Tracking ID:</strong> AR-2025-0921-A</p>
            <p><strong>Est. Completion:</strong> 2025-11-29 14:00 UTC</p>
        </div>

        <div class="p-4 brutalist-border brutalist-shadow-sm bg-gray-900 mb-4">
            <h4 class="text-xs font-bold uppercase mb-2">Alternative: Launch Pre-Confirmed</h4>
            <p class="text-xs font-mono text-gray-400 mb-3">
                Skip the appraisal wait and list immediately with an automated lower reserve value.
            </p>
            <button onclick="launchPreConfirmed()"
                class="bg-purple-600 text-white px-4 py-2 text-xs font-bold uppercase hover:bg-purple-700 brutalist-shadow-sm brutalist-active brutalist-border border-purple-800">
                Launch Immediately
            </button>
        </div>

        <button onclick="resetAppraisalForm()"
            class="w-full bg-white text-black py-2 text-xs font-bold uppercase hover:bg-gray-200 brutalist-shadow-sm brutalist-active brutalist-border">
            Submit Another Artifact
        </button>
    </div>
</div>

<style>
    /* Custom CSS for the toggle switch */
    .toggle-checkbox {
        right: 0;
        border-color: black;
    }

    .toggle-checkbox:checked {
        right: 0;
        border-color: black;
    }

    .toggle-checkbox {
        right: 1.25rem;
        top: -1px;
        transition: right 0.1s ease-in-out, background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
    }

    .toggle-checkbox:checked+.toggle-label {
        background-color: black;
    }
</style>

<script>
    function initSellArtifact() {
        renderFileList();
        setupDragAndDrop();
        resetAppraisalForm(); 

        document.getElementById('is-private-sale').checked = false;
        toggleSecretCodeField();
    }

    // --- Drag and Drop Handlers ---
    function handleFiles(files) {
        // Filter and append new files to the global array
        const newFiles = Array.from(files).filter(file => file.type.startsWith('image/'));
        uploadedFiles = [...uploadedFiles, ...newFiles];
        
        // Limit to 5 files
        if(uploadedFiles.length > 5) {
            uploadedFiles = uploadedFiles.slice(0, 5);
            showToast('Max 5 images allowed.', 'alert-circle', 'red');
        }
        
        renderFileList();
    }

    function renderFileList() {
        const list = document.getElementById('file-list');
        const placeholder = document.getElementById('file-list-placeholder');
        list.innerHTML = '';

        if (uploadedFiles.length === 0) {
            placeholder.classList.remove('hidden');
            return;
        } else {
            placeholder.classList.add('hidden');
        }

        uploadedFiles.forEach((file, index) => {
            const imgSrc = URL.createObjectURL(file);
            const sizeKB = (file.size / 1024).toFixed(1);
            
            list.innerHTML += `
           <div class="relative group brutalist-border bg-gray-50 p-1">
    <div class="h-32 flex items-center justify-center overflow-hidden">
        <img 
          src="${imgSrc}" 
          class="max-h-full max-w-full object-contain border border-gray-200" 
        >
    </div>

    <div class="mt-1 flex justify-between items-center px-1">
        <span class="text-[10px] font-mono truncate w-20">${file.name}</span>
        <button onclick="removeFile(${index})" class="text-red-600 hover:bg-red-100 p-1 rounded">
            <i data-lucide="trash-2" class="w-3 h-3"></i>
        </button>
    </div>
</div>


            `;
        });
        lucide.createIcons();
    }

    function removeFile(index) {
        uploadedFiles.splice(index, 1);
        renderFileList();
    }

    function setupDragAndDrop() {
        const dropArea = document.getElementById('drop-area');
        if (!dropArea) return; 

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.add('bg-gray-200'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.remove('bg-gray-200'), false);
        });

        dropArea.addEventListener('drop', handleDrop, false);

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }
    }

    // --- Image Processing Logic (Resize & Base64) ---
    function processImage(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    // Resize logic
                    const maxWidth = 400;
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth) {
                        height = Math.round(height * (maxWidth / width));
                        width = maxWidth;
                    }

                    canvas.width = width;
                    canvas.height = height;

                    // Draw image on canvas
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert to Base64
                    const dataUrl = canvas.toDataURL(file.type); // Keeps original MIME type (png/jpg)
                    resolve(dataUrl);
                };
                img.onerror = (err) => reject(err);
            };
            reader.onerror = (err) => reject(err);
        });
    }

    // --- Toggle Logic ---
    function toggleSecretCodeField() {
        const isPrivate = document.getElementById('is-private-sale').checked;
        const codeField = document.getElementById('secret-code-field');
        const codeInput = document.getElementById('private-sale-code');
        const confirmCodeInput = document.getElementById('confirm-private-sale-code');

        if (isPrivate) {
            codeField.classList.remove('hidden');
            codeInput.focus();
        } else {
            codeField.classList.add('hidden');
            codeInput.value = '';
            confirmCodeInput.value = '';
        }
    }

    function updateVisibility(artifactId, newVisibility) {
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
                console.log("Updated!");
            } else {
                console.error("Failed:", data.message);
            }
        })
        .catch(err => console.error("Error:", err));
    }

    // --- Submission Logic ---
    async function submitAppraisal() {
        const title = document.getElementById('artifact-title').value.trim();
        const description = document.getElementById('artifact-description').value.trim();
        const isPrivate = document.getElementById('is-private-sale').checked;
        const privateCode = document.getElementById('private-sale-code').value.trim();
        const confirmPrivateCode = document.getElementById('confirm-private-sale-code').value.trim();
        const submitBtn = document.getElementById('submit-btn');

        if (!title || !description || uploadedFiles.length === 0) {
            showToast('Please fill in all fields and upload at least one image.', 'x-circle', 'red');
            return;
        }

        const codeRegex = /^\d{6}$/; 
        if (isPrivate) {
            if (!privateCode || !confirmPrivateCode || !codeRegex.test(privateCode) || !codeRegex.test(confirmPrivateCode)) {
                showToast('Please enter a valid 6-digit Secret Access Code.', 'x-circle', 'red');
                return;
            }
            if (privateCode !== confirmPrivateCode) {
                showToast('Secret codes do not match.', 'x-circle', 'red');
                return;
            }
        }

        // Disable button to prevent double submit
        submitBtn.disabled = true;
        submitBtn.innerText = "Processing Images...";

        try {
            // Process all images to 400px Base64
            const base64Images = await Promise.all(uploadedFiles.map(file => processImage(file)));

            const formData = new FormData();
            formData.append('name', title);
            formData.append('description', description);
            formData.append('is_private', isPrivate ? 1 : 0);
            formData.append('password', privateCode);
            formData.append('confirm_password', confirmPrivateCode);
            
            // Append Base64 strings as an array
            base64Images.forEach((b64, index) => {
                formData.append('images[]', b64);
            });

            submitBtn.innerText = "Uploading...";

            const response = await fetch('/api/submit_artifact.php', {
                method: 'POST',
                body: formData,
                credentials: 'include' 
            });

            const data = await response.json();

            if (data.success) {
                sessionStorage.setItem('recent_artifact_tracking_id', data.tracking_id);
                
                // Switch Views
                document.getElementById('appraisal-form-container').classList.add('hidden');
                document.getElementById('appraisal-status-container').classList.remove('hidden');
                showToast('Artifact submitted successfully!', 'check-circle', 'green');
            } else {
                showToast(`❌ Error: ${data.message}`);
            }
        } catch (error) {
            console.error('Request failed:', error);
            showToast('Something went wrong. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = "SUBMIT FOR APPRAISAL";
        }
    }

    function launchPreConfirmed() {
        const tracking_id = sessionStorage.getItem('recent_artifact_tracking_id');
        if(tracking_id) {
            const real_id = Number(tracking_id.split("-")[2]);
            updateVisibility(real_id, 1);
        }
        showToast('Artifact instantly listed at lower value.', 'zap', 'purple');
        resetAppraisalForm();
    }

    function resetAppraisalForm() {
        document.getElementById('artifact-title').value = '';
        document.getElementById('artifact-description').value = '';
        document.getElementById('is-private-sale').checked = false;
        document.getElementById('private-sale-code').value = '';
        document.getElementById('confirm-private-sale-code').value = '';
        toggleSecretCodeField();

        uploadedFiles = [];
        renderFileList();

        document.getElementById('appraisal-form-container').classList.remove('hidden');
        document.getElementById('appraisal-status-container').classList.add('hidden');
    }
</script>

<?php $slot = ob_get_clean();
include 'app/components/layouts/base.php';
?>