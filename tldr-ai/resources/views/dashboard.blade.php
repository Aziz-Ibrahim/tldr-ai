<x-app-layout>

    <x-slot name="header">

        <h2 class="font-semibold text-xl text-gray-900 dark:text-gray-200 leading-tight">

            Dashboard

        </h2>

    </x-slot>



    <div class="py-6 px-4 max-w-7xl mx-auto">



        {{-- Success message --}}

        @if(session('success'))

            <div class="mb-4 p-4 bg-green-100 text-green-800 dark:bg-green-600 dark:text-white rounded">

                {{ session('success') }}

            </div>

        @endif



        {{-- Show/hide upload form --}}

        <div class="mb-4">

            <a href="#" id="show-upload-form" class="text-blue-600 dark:text-blue-400 hover:underline">Upload New Document</a>

            <div id="upload-form" class="mt-2 p-4 bg-gray-100 dark:bg-gray-800 rounded" style="display: none;">

                <form id="ajax-upload-form" enctype="multipart/form-data">

                    @csrf

                    <input type="file" name="document" required class="mb-2 p-2 rounded text-gray-900 dark:text-black w-full bg-white">

                    <button type="submit" id="upload-btn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:bg-gray-300 dark:disabled:bg-gray-500 disabled:cursor-not-allowed transition-colors">

                        <span id="btn-text">Upload</span>

                        <span id="btn-spinner" class="hidden ml-2">

                            <svg class="animate-spin h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">

                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>

                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>

                            </svg>

                        </span>

                    </button>

                </form>

            </div>



            <div id="upload-feedback" class="mt-2 text-green-600 dark:text-green-400"></div>

        </div>



        <h3 class="text-gray-900 dark:text-gray-200 text-lg font-semibold mb-4">Uploaded Documents</h3>



        {{-- Sorting and controls --}}

        <div class="mb-4 flex flex-wrap gap-2 items-center">

            <span class="text-gray-700 dark:text-gray-400 text-sm">Sort by:</span>

            <select id="sort-select" class="px-3 py-1 bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white text-sm rounded border border-gray-300 dark:border-gray-500 focus:border-blue-500">

                <option value="date-desc">Date (Newest)</option>

                <option value="date-asc">Date (Oldest)</option>

                <option value="name-asc">Name (A-Z)</option>

                <option value="name-desc">Name (Z-A)</option>

                <option value="size-desc">Size (Largest)</option>

                <option value="size-asc">Size (Smallest)</option>

                <option value="type-asc">File Type</option>

            </select>

        </div>



        @if(count($files) === 0)

            <p class="text-gray-700 dark:text-gray-400">No documents uploaded yet.</p>

        @else

            <ul id="uploaded-files" class="space-y-4">

                @foreach($files as $file)

                    <li class="p-4 bg-gray-100 dark:bg-gray-700 rounded" 

                        data-document-id="{{ $file['id'] }}" 

                        data-name="{{ $file['original_name'] }}" 

                        data-date="{{ $file['updated_at'] }}" 

                        data-size="{{ $file['size'] }}" 

                        data-type="{{ pathinfo($file['original_name'], PATHINFO_EXTENSION) }}">



                        <div class="flex justify-between items-start mb-2">

                            <strong class="text-gray-900 dark:text-white">{{ $file['original_name'] }}</strong>

                            <button class="delete-file-btn text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors" 

                                    data-document-id="{{ $file['id'] }}" 

                                    data-filename="{{ $file['original_name'] }}">

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>

                                </svg>

                            </button>

                        </div>



                        <p class="text-gray-700 dark:text-gray-300 text-sm mt-1">

                            <span class="text-gray-600 dark:text-gray-400">Size:</span> {{ $file['size'] }} | 

                            <span class="text-gray-600 dark:text-gray-400">Type:</span> {{ strtoupper(pathinfo($file['original_name'], PATHINFO_EXTENSION)) }} |

                            <span class="text-gray-600 dark:text-gray-400">Uploaded:</span> {{ $file['updated_at'] }}

                        </p>



                        <p class="text-gray-700 dark:text-gray-300 mt-1">

                            <em>Summary:</em> 

                            <span class="summary-text">{{ Str::limit($file['summary'], 60) }}</span>

                            @if($file['summary'] === 'Click to generate summary')

                                <button class="generate-summary-btn ml-2 px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">

                                    Generate

                                </button>

                            @else

                                <button class="view-summary-btn ml-2 px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors" 

                                        data-summary="{{ str_replace('"', '&quot;', $file['summary']) }}">

                                    View Full Summary

                                </button>

                            @endif

                        </p>

                        

                        {{-- Expandable summary section --}}

                        <div class="summary-details hidden mt-3 p-3 bg-gray-50 dark:bg-gray-600 rounded-lg border-l-4 border-green-500">

                            <div class="flex justify-between items-start mb-2">

                                <h4 class="text-gray-900 dark:text-white font-medium">Full Summary</h4>

                                <button class="close-summary-btn text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors">

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>

                                    </svg>

                                </button>

                            </div>

                            <p class="text-gray-800 dark:text-gray-200 text-sm leading-relaxed summary-full-text"></p>

                        </div>

                        

                        <a href="{{ $file['url'] ?? '#' }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline mt-2 block transition-colors">View Document</a>

                    </li>

                @endforeach

            </ul>

        @endif

    </div>

    {{-- JS for toggle form --}}
    <script>
        const toggleBtn = document.getElementById('show-upload-form');
        const uploadForm = document.getElementById('upload-form');

        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if(uploadForm.style.display === 'none') {
                uploadForm.style.display = 'block';
            } else {
                uploadForm.style.display = 'none';
            }
        });
    </script>
    
    {{-- Upload script with dynamic progress feedback --}}
    <script>
        let progressInterval;
        let currentStep = 0;
        
        const progressSteps = [
            { text: 'Uploading to cloud storage...', dots: true },
            { text: 'Extracting content...', dots: true },
            { text: 'AI is thinking...', dots: true },
            { text: 'Trying alternative approach...', dots: true },
            { text: 'Hang in there, almost ready...', dots: true },
            { text: 'Finalizing results...', dots: true }
        ];

        function updateProgress(step, customText = null) {
            const feedback = document.getElementById('upload-feedback');
            const text = customText || progressSteps[step]?.text || 'Processing...';
            
            // Clear any existing dot interval first
            const existingDotInterval = feedback.getAttribute('data-dot-interval');
            if (existingDotInterval) {
                clearInterval(parseInt(existingDotInterval));
                feedback.removeAttribute('data-dot-interval');
            }
            
            if (progressSteps[step]?.dots && !customText) {
                let dots = '';
                const dotInterval = setInterval(() => {
                    dots += '.';
                    if (dots.length > 10) dots = '';
                    feedback.textContent = text + dots;
                }, 200);
                
                // Store interval for cleanup
                feedback.setAttribute('data-dot-interval', dotInterval);
            } else {
                feedback.textContent = text;
            }
        }

        function clearProgress() {
            const feedback = document.getElementById('upload-feedback');
            const dotInterval = feedback.getAttribute('data-dot-interval');
            if (dotInterval) {
                clearInterval(parseInt(dotInterval));
                feedback.removeAttribute('data-dot-interval');
            }
            if (progressInterval) {
                clearInterval(progressInterval);
                progressInterval = null;
            }
            currentStep = 0;
        }

        document.getElementById('ajax-upload-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const feedback = document.getElementById('upload-feedback');
            const uploadBtn = document.getElementById('upload-btn');
            const btnText = document.getElementById('btn-text');
            const btnSpinner = document.getElementById('btn-spinner');
            const fileInput = form.querySelector('input[type="file"]');

            // Validate file selection and type
            if (!fileInput.files.length) {
                feedback.textContent = 'Please select a file first.';
                feedback.className = 'mt-2 text-red-400';
                return;
            }

            const file = fileInput.files[0];
            const allowedTypes = ['txt', 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            const fileExt = file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(fileExt)) {
                feedback.textContent = 'Please select a valid file type (TXT, PDF, DOC, DOCX, JPG, PNG).';
                feedback.className = 'mt-2 text-red-400';
                return;
            }

            // Start upload process with dynamic feedback
            currentStep = 0;
            uploadBtn.disabled = true;
            btnText.textContent = 'Working...';
            btnSpinner.classList.remove('hidden');
            fileInput.disabled = true;
            feedback.className = 'mt-2 text-blue-400';

            // Step 1: Uploading
            updateProgress(0);
            
            // Auto-progress through steps during upload
            progressInterval = setInterval(() => {
                currentStep++;
                if (currentStep < progressSteps.length) {
                    updateProgress(currentStep);
                }
            }, 3000); // Change step every 3 seconds

            fetch("{{ route('dashboard.upload') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                clearProgress();
                
                if(data.success) {
                    // Success with celebration - use updateProgress to properly clear dots
                    updateProgress(null, 'Done! Your document has been uploaded and summarized!');
                    feedback.className = 'mt-2 text-green-400 font-semibold';

                    // Update file list
                    const list = document.querySelector('#uploaded-files');
                    const noFilesMsg = document.querySelector('p.text-gray-400');
                    if (noFilesMsg && noFilesMsg.textContent.includes('No documents uploaded yet')) {
                        noFilesMsg.remove();
                    }

                    let filesList = document.getElementById('uploaded-files');
                    if (!filesList) {
                        filesList = document.createElement('ul');
                        filesList.id = 'uploaded-files';
                        filesList.className = 'space-y-4';
                        document.querySelector('.py-6').appendChild(filesList);
                    }

                    const li = document.createElement('li');
                    li.classList.add(
                        'p-4',
                        'rounded',
                        'bg-white', 'dark:bg-gray-700',  // light + dark background
                        'shadow'
                    );
                    li.setAttribute('data-document-id', data.file.id);
                    li.setAttribute('data-name', data.file.original_name);
                    li.setAttribute('data-date', 'Just now');
                    li.setAttribute('data-size', data.file.size);
                    li.setAttribute('data-type', data.file.original_name.split('.').pop().toLowerCase());

                    const truncatedSummary =
                        data.file.summary.length > 60
                            ? data.file.summary.substring(0, 60) + '...'
                            : data.file.summary;

                    li.innerHTML = `
                        <div class="flex justify-between items-start mb-2">
                            <strong class="text-gray-900 dark:text-white">${data.file.original_name}</strong>
                            <button class="delete-file-btn text-red-600 dark:text-red-400 hover:text-red-500 dark:hover:text-red-300 transition-colors" 
                                    data-document-id="${data.file.id}" 
                                    data-filename="${data.file.original_name}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 text-sm mt-1">
                            <span class="text-gray-500 dark:text-gray-400">Size:</span> ${data.file.size} | 
                            <span class="text-gray-500 dark:text-gray-400">Type:</span> ${data.file.original_name.split('.').pop().toUpperCase()} |
                            <span class="text-gray-500 dark:text-gray-400">Uploaded:</span> Just now
                        </p>
                        <p class="text-gray-700 dark:text-gray-300 mt-1">
                            <em>Summary:</em> <span class="summary-text">${truncatedSummary}</span>
                            ${
                                data.file.summary === 'Click to generate summary'
                                    ? `<button class="generate-summary-btn ml-2 px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">Generate</button>`
                                    : `<button class="view-summary-btn ml-2 px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors" data-summary="${data.file.summary.replace(/"/g, '&quot;')}">View Full Summary</button>`
                            }
                        </p>
                        <div class="summary-details hidden mt-3 p-3 rounded-lg border-l-4 border-green-500 bg-gray-100 dark:bg-gray-600">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-gray-900 dark:text-white font-medium">Full Summary</h4>
                                <button class="close-summary-btn text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-gray-800 dark:text-gray-200 text-sm leading-relaxed summary-full-text"></p>
                        </div>
                        <a href="${data.file.url}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline mt-2 block transition-colors">View Document</a>
                    `;

                    filesList.appendChild(li);

                    
                    // Reset form
                    form.reset();
                    
                    // Hide upload form after successful upload
                    setTimeout(() => {
                        document.getElementById('upload-form').style.display = 'none';
                        feedback.textContent = '';
                    }, 4000);
                    
                } else {
                    feedback.textContent = 'Upload failed: ' + (data.message || 'Unknown error');
                    feedback.className = 'mt-2 text-red-400';
                }
            })
            .catch(err => {
                clearProgress();
                console.error('Upload error:', err);
                feedback.textContent = 'Error uploading file: ' + err.message;
                feedback.className = 'mt-2 text-red-400';
            })
            .finally(() => {
                uploadBtn.disabled = false;
                btnText.textContent = 'Upload';
                btnSpinner.classList.add('hidden');
                fileInput.disabled = false;
            });
        });
    </script>

    {{-- Summary and delete functionality --}}
    <script>
        document.addEventListener('click', function(e) {
            // Handle Generate Summary button
            if (e.target.classList.contains('generate-summary-btn')) {
                const button = e.target;
                const listItem = button.closest('li');
                const documentId = listItem.getAttribute('data-document-id');
                const summarySpan = listItem.querySelector('.summary-text');
                
                // Update button state
                const originalText = button.textContent;
                button.textContent = 'Generating...';
                button.disabled = true;
                button.classList.add('opacity-50');
                
                fetch("{{ route('dashboard.generate-summary') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        document_id: documentId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update the truncated summary text
                        const truncatedSummary = data.summary.length > 60 ? data.summary.substring(0, 60) + '...' : data.summary;
                        summarySpan.textContent = truncatedSummary;
                        
                        // Replace Generate button with View Full Summary button
                        button.outerHTML = '<button class="view-summary-btn ml-2 px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors" data-summary="' + data.summary.replace(/"/g, '&quot;') + '">View Full Summary</button>';
                        
                        // Auto-expand the summary
                        showSummaryDetails(listItem, data.summary);
                    } else {
                        summarySpan.textContent = 'Summary generation failed';
                        button.textContent = 'Retry';
                        button.disabled = false;
                        button.classList.remove('opacity-50');
                    }
                })
                .catch(err => {
                    console.error('Summary generation error:', err);
                    summarySpan.textContent = 'Summary generation failed';
                    button.textContent = originalText;
                    button.disabled = false;
                    button.classList.remove('opacity-50');
                });
            }
            
            // Handle View Full Summary button
            if (e.target.classList.contains('view-summary-btn')) {
                const button = e.target;
                const listItem = button.closest('li');
                const summary = button.getAttribute('data-summary');
                const summaryDetails = listItem.querySelector('.summary-details');
                
                if (summaryDetails.classList.contains('hidden')) {
                    showSummaryDetails(listItem, summary);
                } else {
                    hideSummaryDetails(listItem);
                }
            }
            
            // Handle Close Summary button
            if (e.target.closest('.close-summary-btn')) {
                const listItem = e.target.closest('li');
                hideSummaryDetails(listItem);
            }
            
            // Handle Delete File button
            if (e.target.closest('.delete-file-btn')) {
                const button = e.target.closest('.delete-file-btn');
                const documentId = button.getAttribute('data-document-id');
                const filename = button.getAttribute('data-filename');
                
                if (confirm('Are you sure you want to delete "' + filename + '"? This action cannot be undone.')) {
                    deleteFile(documentId, button.closest('li'));
                }
            }
        });

        // Sorting functionality
        document.getElementById('sort-select').addEventListener('change', function() {
            const sortBy = this.value;
            const filesList = document.getElementById('uploaded-files');
            const listItems = Array.from(filesList.children);
            
            listItems.sort((a, b) => {
                let aVal, bVal;
                
                switch (sortBy) {
                    case 'name-asc':
                        return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
                    case 'name-desc':
                        return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
                    case 'date-asc':
                        return new Date(a.getAttribute('data-date')) - new Date(b.getAttribute('data-date'));
                    case 'date-desc':
                        return new Date(b.getAttribute('data-date')) - new Date(a.getAttribute('data-date'));
                    case 'size-asc':
                        aVal = parseFloat(a.getAttribute('data-size'));
                        bVal = parseFloat(b.getAttribute('data-size'));
                        return aVal - bVal;
                    case 'size-desc':
                        aVal = parseFloat(a.getAttribute('data-size'));
                        bVal = parseFloat(b.getAttribute('data-size'));
                        return bVal - aVal;
                    case 'type-asc':
                        return a.getAttribute('data-type').localeCompare(b.getAttribute('data-type'));
                    default:
                        return 0;
                }
            });
            
            // Re-append sorted items
            listItems.forEach(item => filesList.appendChild(item));
        });

        // Function to show summary details with smooth animation
        function showSummaryDetails(listItem, summary) {
            const summaryDetails = listItem.querySelector('.summary-details');
            const summaryFullText = listItem.querySelector('.summary-full-text');
            const viewBtn = listItem.querySelector('.view-summary-btn');
            
            // Set the full summary text
            summaryFullText.textContent = summary;
            
            // Show with smooth animation
            summaryDetails.classList.remove('hidden');
            summaryDetails.style.maxHeight = '0px';
            summaryDetails.style.opacity = '0';
            summaryDetails.style.overflow = 'hidden';
            summaryDetails.style.transition = 'all 0.3s ease-in-out';
            
            // Trigger animation
            requestAnimationFrame(() => {
                summaryDetails.style.maxHeight = summaryDetails.scrollHeight + 'px';
                summaryDetails.style.opacity = '1';
            });
            
            // Update button text
            if (viewBtn) {
                viewBtn.textContent = 'Hide Summary';
            }
        }

        // Function to hide summary details with smooth animation
        function hideSummaryDetails(listItem) {
            const summaryDetails = listItem.querySelector('.summary-details');
            const viewBtn = listItem.querySelector('.view-summary-btn');
            
            // Hide with smooth animation
            summaryDetails.style.maxHeight = '0px';
            summaryDetails.style.opacity = '0';
            
            setTimeout(() => {
                summaryDetails.classList.add('hidden');
                summaryDetails.style.maxHeight = '';
                summaryDetails.style.opacity = '';
                summaryDetails.style.overflow = '';
                summaryDetails.style.transition = '';
            }, 300);
            
            // Update button text
            if (viewBtn) {
                viewBtn.textContent = 'View Full Summary';
            }
        }

        // Delete file function
        function deleteFile(documentId, listItem) {
            const deleteBtn = listItem.querySelector('.delete-file-btn');
            const originalIcon = deleteBtn.innerHTML;
            
            // Show loading state
            deleteBtn.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            deleteBtn.disabled = true;
            
            fetch("{{ route('dashboard.delete') }}", {
                method: "DELETE",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    document_id: documentId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Remove item with animation
                    listItem.style.transition = 'all 0.3s ease-out';
                    listItem.style.transform = 'translateX(-100%)';
                    listItem.style.opacity = '0';
                    
                    setTimeout(() => {
                        listItem.remove();
                        
                        // Show "no documents" message if list is empty
                        const filesList = document.getElementById('uploaded-files');
                        if (filesList && filesList.children.length === 0) {
                            const noFilesMsg = document.createElement('p');
                            noFilesMsg.className = 'text-gray-400';
                            noFilesMsg.textContent = 'No documents uploaded yet.';
                            filesList.parentNode.insertBefore(noFilesMsg, filesList);
                        }
                    }, 300);
                } else {
                    alert('Failed to delete file: ' + (data.message || 'Unknown error'));
                    deleteBtn.innerHTML = originalIcon;
                    deleteBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error('Delete error:', err);
                alert('Error deleting file: ' + err.message);
                deleteBtn.innerHTML = originalIcon;
                deleteBtn.disabled = false;
            });
        }
    </script>

</x-app-layout>