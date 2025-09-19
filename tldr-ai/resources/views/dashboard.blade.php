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
                    <li class="p-4 bg-gray-700 rounded">
                        <strong class="text-white">{{ $file['name'] }}</strong>
                        <p class="text-gray-300 mt-1"><em>Summary:</em> {{ $file['summary'] }}</p>
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
    <script>
        document.getElementById('ajax-upload-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const feedback = document.getElementById('upload-feedback');

            fetch("{{ route('dashboard.upload') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    feedback.textContent = data.message;

                    // Append new file to the list
                    const list = document.querySelector('#uploaded-files');
                    const li = document.createElement('li');
                    li.classList.add('p-4','bg-gray-700','rounded','mt-2');
                    li.innerHTML = `<strong class="text-white">${data.file.name}</strong>
                                    <p class="text-gray-300 mt-1"><em>Summary:</em> ${data.file.summary}</p>
                                    <a href="${data.file.url}" target="_blank" class="text-blue-400 hover:underline mt-2 block">View Document</a>`;
                    list.appendChild(li);
                } else {
                    feedback.textContent = 'Upload failed.';
                }
            })
            .catch(err => {
                feedback.textContent = 'Error uploading file.';
                console.error(err);
            });
        });
    </script>

</x-app-layout>
