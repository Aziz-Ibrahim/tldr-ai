<x-app-layout>
    <x-slot name="header">
        <h2>Dashboard</h2>
    </x-slot>

    <div>
        <p>Welcome, {{ Auth::user()->name }}!</p>
        <a href="/upload">Upload a new document</a>
    </div>
</x-app-layout>
