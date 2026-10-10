$ErrorActionPreference = 'Stop'

$infraDirectory = Resolve-Path (Join-Path $PSScriptRoot '..\..\test-manager-library-3413974-infra')
$apiDirectory = Resolve-Path (Join-Path $PSScriptRoot '..')
Push-Location $infraDirectory
try {
    docker compose run --rm --volume "${apiDirectory}:/var/www/html" --entrypoint sh api -c 'if [ ! -x vendor/bin/phpunit ]; then composer install --no-interaction --prefer-dist; fi && php artisan migrate --force && php artisan test'
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}
finally {
    Pop-Location
}
