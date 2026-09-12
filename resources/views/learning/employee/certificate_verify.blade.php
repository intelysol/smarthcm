<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification — Flow HCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 p-6 text-center text-white">
            <div class="w-14 h-14 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-3 shadow-md">
                <i class="fa-solid fa-certificate text-2xl text-white"></i>
            </div>
            <h1 class="text-xl font-bold">Certificate Verification</h1>
            <p class="text-xs text-slate-400 mt-1">Flow HCM Enterprise Learning & Certification Registry</p>
        </div>

        <div class="p-6">
            @if($result['valid'])
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-2xl"></i>
                    <div>
                        <div class="font-bold text-emerald-900 text-sm">Official Credential Verified</div>
                        <div class="text-xs text-emerald-700">This certificate is active and authentic.</div>
                    </div>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Recipient</span>
                        <span class="font-semibold text-slate-900">{{ $result['recipient_name'] }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Course / Program</span>
                        <span class="font-semibold text-slate-900">{{ $result['course_title'] }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Certificate Number</span>
                        <span class="font-mono text-xs font-semibold text-slate-700">{{ $result['certificate_number'] }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Issuing Body</span>
                        <span class="font-semibold text-slate-900">{{ $result['issuing_body'] }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Issue Date</span>
                        <span class="font-semibold text-slate-900">{{ $result['issued_at'] }}</span>
                    </div>
                    @if(!empty($result['expiry_date']))
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Valid Until</span>
                        <span class="font-semibold text-slate-900">{{ $result['expiry_date'] }}</span>
                    </div>
                    @endif
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-3xl mb-2"></i>
                    <div class="font-bold text-red-900 text-sm">Invalid or Expired Certificate</div>
                    <p class="text-xs text-red-700 mt-1">{{ $result['message'] ?? 'The requested credential could not be verified.' }}</p>
                </div>
            @endif

            <div class="mt-8 text-center">
                <span class="text-[11px] text-slate-400">
                    <i class="fa-solid fa-lock mr-1"></i>Protected by Flow HCM Digital Credential Registry. Zero personal sensitive data exposed.
                </span>
            </div>
        </div>
    </div>
</body>
</html>
