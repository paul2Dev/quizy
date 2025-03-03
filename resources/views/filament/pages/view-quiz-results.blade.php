<x-filament-panels::page>
    <div class="space-y-6">
        <h1 class="text-xl font-bold">{{ $quiz->title }}</h1>

        <!-- Display Quiz Form -->
        {{ $this->form }}

        <!-- Show Correct Answers -->
        {{-- <div class="mt-6 p-4 bg-green-100 rounded-lg">
            <h2 class="text-lg font-semibold">Correct Answers</h2>
            <ul class="list-disc pl-6">
                @foreach ($quiz->questions as $question)
                    <li class="mt-2">
                        <strong>{{ $question->question_text }}</strong>
                        <br>
                        Correct Answer: 
                        @foreach ($question->options->where('is_correct', true) as $option)
                            <span class="text-green-700 font-semibold">{{ $option->option_text }}</span>
                        @endforeach
                    </li>
                @endforeach
            </ul>
        </div> --}}
    </div>
</x-filament-panels::page>
