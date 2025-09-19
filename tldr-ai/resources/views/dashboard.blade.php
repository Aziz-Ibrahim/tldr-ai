<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-6 px-4 max-w-7xl mx-auto">

        {{-- Success message --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-600 text-white rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- Show/hide upload form --}}
        <div class="mb-4">
            <a href="#" id="show-upload-form" class="text-blue-400 hover:underline">Upload New Document</a>
            <div id="upload-form" class="mt-2 p-4 bg-gray-800 rounded" style="display: none;">
                <form id="ajax-upload-form" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="document" required class="mb-2 p-2 rounded text-black w-full">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
                </form>
            </div>

            <div id="upload-feedback" class="mt-2 text-green-400"></div>
        </div>

        <h3 class="text-gray-200 text-lg font-semibold mb-2">Uploaded Documents</h3>

        @if(count($files) === 0)
            <p class="text-gray-400">No documents uploaded yet.</p>
        @else
            <ul id="uploaded-files" class="space-y-4">
                @foreach($files as $file)
                    <li class="p-4 bg-gray-700 rounded" data-filename="{{ $file['name'] }}">
                        <strong class="text-white">{{ $file['name'] }}</strong>
                        <p class="text-gray-300 mt-1">
                            <em>Summary:</em> 
                            <span class="summary-text">{{ $file['summary'] }}</span>
                            @if($file['summary'] === 'Click to generate summary')
                                <button class="generate-summary-btn ml-2 px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                    Generate
                                </button>
                            @endif
                        </p>
                        <a href="{{ $file['url'] ?? '#' }}" target="_blank" class="text-blue-400 hover:underline mt-2 block">View Document</a>
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
    
    {{-- Upload script --}}
    <script>
        document.getElementById('ajax-upload-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const feedback = document.getElementById('upload-feedback');
            
            feedback.textContent = 'Uploading...';
            feedback.className = 'mt-2 text-blue-400';

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
                if(data.success) {
                    feedback.textContent = data.message;
                    feedback.className = 'mt-2 text-green-400';

                    // Handle UI updates...
                    const list = document.querySelector('#uploaded-files');
                    const noFilesMsg = document.querySelector('p.text-gray-400');
                    if(noFilesMsg && noFilesMsg.textContent.includes('No documents uploaded yet')) {
                        noFilesMsg.remove();
                    }
                    
                    let filesList = document.getElementById('uploaded-files');
                    if(!filesList) {
                        filesList = document.createElement('ul');
                        filesList.id = 'uploaded-files';
                        filesList.className = 'space-y-4';
                        document.querySelector('.py-6').appendChild(filesList);
                    }
                    
                    const li = document.createElement('li');
                    li.classList.add('p-4','bg-gray-700','rounded');
                    li.innerHTML = `<strong class="text-white">${data.file.name}</strong>
                                    <p class="text-gray-300 mt-1"><em>Summary:</em> <span class="summary-text">${data.file.summary}</span></p>
                                    <a href="${data.file.url}" target="_blank" class="text-blue-400 hover:underline mt-2 block">View Document</a>`;
                    filesList.appendChild(li);
                    
                    this.reset();
                } else {
                    feedback.textContent = 'Upload failed: ' + (data.message || 'Unknown error');
                    feedback.className = 'mt-2 text-red-400';
                }
            })
            .catch(err => {
                console.error('Upload error:', err);
                feedback.textContent = 'Error uploading file: ' + err.message;
                feedback.className = 'mt-2 text-red-400';
            });
        });
    </script>

    {{-- Summary generation script --}}
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('generate-summary-btn')) {
                const button = e.target;
                const listItem = button.closest('li');
                const fileName = listItem.getAttribute('data-filename');
                const summarySpan = listItem.querySelector('.summary-text');
                
                // Update button state
                button.textContent = 'Generating...';
                button.disabled = true;
                
                fetch("{{ route('dashboard.generate-summary') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        file_name: fileName
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        summarySpan.textContent = data.summary;
                        button.remove(); // Remove button after successful generation
                    } else {
                        summarySpan.textContent = 'Summary generation failed';
                        button.textContent = 'Retry';
                        button.disabled = false;
                    }
                })
                .catch(err => {
                    console.error('Summary generation error:', err);
                    summarySpan.textContent = 'Summary generation failed';
                    button.textContent = 'Retry';
                    button.disabled = false;
                });
            }
        });
    </script>

</x-app-layout>