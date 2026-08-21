<#
.SYNOPSIS
    DevFolio.AI Auto-Push Watcher
    Watches the repository for file changes and automatically commits and pushes them to GitHub.

.EXAMPLE
    .\autopush.ps1
    .\autopush.ps1 -IntervalSeconds 5
#>

param(
    [int]$IntervalSeconds = 5,
    [string]$Branch = "main",
    [string]$Remote = "origin"
)

$Host.UI.RawUI.WindowTitle = "Git Auto-Push Watcher -> $Remote/$Branch"

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "   🚀 DevFolio.AI - Git Auto-Push Watcher Active" -ForegroundColor Green
Write-Host "   Branch: $Branch | Remote: $Remote | Interval: ${IntervalSeconds}s" -ForegroundColor Yellow
Write-Host "   Press Ctrl + C to stop watching at any time." -ForegroundColor DarkGray
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""

while ($true) {
    # Check for modified, deleted, or untracked files
    $status = git status --porcelain
    
    if ($status) {
        $timestamp = (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
        $changeCount = ($status -split "`n").Count
        
        Write-Host "[$timestamp] 📦 Detected $changeCount file change(s)..." -ForegroundColor Yellow
        
        # Stage all changes
        git add -A
        
        # Create descriptive commit message with timestamp
        $commitMessage = "auto-sync: $timestamp - updated $changeCount file(s)"
        git commit -m $commitMessage
        
        Write-Host "[$timestamp] 🚀 Pushing changes to $Remote/$Branch..." -ForegroundColor Cyan
        
        # Push to remote repository
        $pushResult = git push $Remote $Branch 2>&1
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "[$timestamp] ✅ Successfully pushed to GitHub!" -ForegroundColor Green
        } else {
            Write-Host "[$timestamp] ⚠️ Push encountered an issue: $pushResult" -ForegroundColor Red
        }
        Write-Host ""
    }
    
    # Wait before checking again
    Start-Sleep -Seconds $IntervalSeconds
}
