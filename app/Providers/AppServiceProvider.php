use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }
}

