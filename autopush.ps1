param(
    [int]$IntervalSeconds = 5,
    [string]$Branch = "main",
    [string]$Remote = "origin"
)

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "   DevFolio.AI - Git Auto-Push Watcher Active" -ForegroundColor Green
Write-Host "   Branch: $Branch | Remote: $Remote | Interval: ${IntervalSeconds}s" -ForegroundColor Yellow
Write-Host "   Press Ctrl + C to stop watching." -ForegroundColor DarkGray
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""

while ($true) {
    $status = git status --porcelain
    
    if ($status) {
        $timestamp = (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
        $changeLines = @($status | Where-Object { $_ -match '\S' })
        $changeCount = $changeLines.Count
        
        Write-Host "[$timestamp] Detected $changeCount file change(s)..." -ForegroundColor Yellow
        
        git add -A
        
        $commitMessage = "auto-sync: $timestamp - updated $changeCount file(s)"
        git commit -m $commitMessage
        
        Write-Host "[$timestamp] Pushing changes to $Remote/$Branch..." -ForegroundColor Cyan
        
        git push $Remote $Branch
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "[$timestamp] [SUCCESS] Successfully pushed to GitHub!" -ForegroundColor Green
        } else {
            Write-Host "[$timestamp] [ERROR] Push failed." -ForegroundColor Red
        }
        Write-Host ""
    }
    
    Start-Sleep -Seconds $IntervalSeconds
}
