# Kukija Admin Panel - Quick Start Script
# Run this script to set up the admin panel

Write-Host "🍪 Kukija Admin Panel Setup" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the correct directory
if (!(Test-Path "artisan")) {
    Write-Host "❌ Error: Please run this script from the Laravel project root directory" -ForegroundColor Red
    exit 1
}

Write-Host "📦 Step 1: Running migrations..." -ForegroundColor Yellow
php artisan migrate

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Migration failed. Please check your database connection." -ForegroundColor Red
    exit 1
}

Write-Host "✅ Migrations completed successfully!" -ForegroundColor Green
Write-Host ""

Write-Host "👤 Step 2: Seeding admin users..." -ForegroundColor Yellow
php artisan db:seed --class=AdminUserSeeder

if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Seeding failed. Admin users may already exist." -ForegroundColor Yellow
} else {
    Write-Host "✅ Admin users created successfully!" -ForegroundColor Green
}

Write-Host ""
Write-Host "🎉 Setup Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Default Admin Credentials:" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host "Super Admin:" -ForegroundColor White
Write-Host "  Email: admin@kukija.com" -ForegroundColor Gray
Write-Host "  Password: admin123" -ForegroundColor Gray
Write-Host ""
Write-Host "Manager:" -ForegroundColor White
Write-Host "  Email: manager@kukija.com" -ForegroundColor Gray
Write-Host "  Password: manager123" -ForegroundColor Gray
Write-Host ""
Write-Host "⚠️  IMPORTANT: Change these passwords in production!" -ForegroundColor Red
Write-Host ""
Write-Host "🚀 Next Steps:" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host "1. Start your development server: php artisan serve" -ForegroundColor White
Write-Host "2. Test admin login: POST /api/admin/login" -ForegroundColor White
Write-Host "3. Build Vue.js frontend components" -ForegroundColor White
Write-Host "4. Read ADMIN_PANEL_README.md for full documentation" -ForegroundColor White
Write-Host ""
Write-Host "📚 API Documentation:" -ForegroundColor Cyan
Write-Host "  Orders: /api/admin/orders" -ForegroundColor Gray
Write-Host "  Customers: /api/admin/customers" -ForegroundColor Gray
Write-Host "  Reports: /api/admin/reports" -ForegroundColor Gray
Write-Host ""
Write-Host "Happy coding! 🍪✨" -ForegroundColor Magenta
