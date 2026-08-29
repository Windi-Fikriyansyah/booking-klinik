@extends('template.app')

@section('content')
    <div class="col-md-12">
        <div class="px-md-2 max-w-6xl mx-auto mb-5">

            <!-- Header Section -->
            @php
                $tenantUser = Auth::user()->tenantUsers()->first();
                $needsOnboarding = false;
                $defaultSubdomain = '';
                if ($tenantUser) {
                    $tenantId = $tenantUser->tenant_id;
                    $needsOnboarding = !\App\Models\WebsiteSetting::where('tenant_id', $tenantId)->exists();
                    $defaultSubdomain = \Illuminate\Support\Str::slug($tenantUser->tenant->name);
                }
            @endphp
        </div>
    </div>

    @if($needsOnboarding)
    <div class="modal fade" id="onboardingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="onboardingModalTitle">Tenant Onboarding</h5>
                </div>
                <div class="modal-body">
                    <div id="step1">
                        <h6>Step 1: Website Setting</h6>
                        <p>Silakan tentukan subdomain untuk website klinik Anda.</p>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="subdomainInput" value="{{ $defaultSubdomain }}" placeholder="nama-klinik">
                            <span class="input-group-text">.domain.com</span>
                        </div>
                        <div id="subdomainFeedback" class="small mb-3"></div>
                        <button class="btn btn-primary w-100" id="btnCheckSubdomain">Cek & Lanjut</button>
                    </div>

                    <div id="step2" class="d-none">
                        <h6>Step 2: Default Operating Hours</h6>
                        <p>Silakan sesuaikan jam operasional klinik Anda:</p>
                        <div id="operatingHoursList">
                            @php
                                $days = [
                                    0 => 'Minggu',
                                    1 => 'Senin',
                                    2 => 'Selasa',
                                    3 => 'Rabu',
                                    4 => 'Kamis',
                                    5 => 'Jumat',
                                    6 => 'Sabtu',
                                ];
                            @endphp
                            @foreach($days as $index => $dayName)
                            <div class="d-flex align-items-center mb-2 op-hour-row" data-day="{{ $index }}">
                                <div class="form-check form-switch me-3" style="width: 100px;">
                                    <input class="form-check-input day-toggle" type="checkbox" id="dayToggle{{ $index }}" {{ $index != 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dayToggle{{ $index }}">{{ $dayName }}</label>
                                </div>
                                <div class="d-flex align-items-center flex-grow-1 time-inputs" style="{{ $index == 0 ? 'opacity: 0.5; pointer-events: none;' : '' }}">
                                    <input type="time" class="form-control me-2 open-time" value="09:00">
                                    <span class="me-2">-</span>
                                    <input type="time" class="form-control close-time" value="20:00">
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button class="btn btn-success w-100 mt-3" id="btnSubmitOnboarding">Selesaikan Onboarding</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('onboardingModal'), {
            backdrop: 'static',
            keyboard: false
        });
        myModal.show();
    
        const btnCheck = document.getElementById('btnCheckSubdomain');
        const inputSubdomain = document.getElementById('subdomainInput');
        const feedback = document.getElementById('subdomainFeedback');
    
        btnCheck.addEventListener('click', async function() {
            const subdomain = inputSubdomain.value.trim();
            if(!subdomain) {
                feedback.innerHTML = '<span class="text-danger">Subdomain tidak boleh kosong.</span>';
                return;
            }
    
            btnCheck.disabled = true;
            btnCheck.innerText = 'Checking...';
            
            try {
                const res = await fetch("{{ route('onboarding.check') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ subdomain: subdomain })
                });
                const data = await res.json();
                
                if(data.available) {
                    feedback.innerHTML = '<span class="text-success">Subdomain tersedia!</span>';
                    document.getElementById('step1').classList.add('d-none');
                    document.getElementById('step2').classList.remove('d-none');
                } else {
                    feedback.innerHTML = '<span class="text-danger">Subdomain sudah digunakan, silakan cari yang lain.</span>';
                    btnCheck.disabled = false;
                    btnCheck.innerText = 'Cek & Lanjut';
                }
            } catch(e) {
                feedback.innerHTML = '<span class="text-danger">Terjadi kesalahan.</span>';
                btnCheck.disabled = false;
                btnCheck.innerText = 'Cek & Lanjut';
            }
        });
    
        document.querySelectorAll('.day-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const timeInputs = this.closest('.op-hour-row').querySelector('.time-inputs');
                if (this.checked) {
                    timeInputs.style.opacity = '1';
                    timeInputs.style.pointerEvents = 'auto';
                } else {
                    timeInputs.style.opacity = '0.5';
                    timeInputs.style.pointerEvents = 'none';
                }
            });
        });

        document.getElementById('btnSubmitOnboarding').addEventListener('click', async function() {
            const btnSubmit = document.getElementById('btnSubmitOnboarding');
            const subdomain = inputSubdomain.value.trim();
            
            const operatingHours = [];
            document.querySelectorAll('.op-hour-row').forEach(row => {
                operatingHours.push({
                    day_of_week: parseInt(row.getAttribute('data-day')),
                    is_open: row.querySelector('.day-toggle').checked,
                    open_time: row.querySelector('.open-time').value || '09:00',
                    close_time: row.querySelector('.close-time').value || '20:00'
                });
            });

            btnSubmit.disabled = true;
            btnSubmit.innerText = 'Menyimpan...';
    
            try {
                const res = await fetch("{{ route('onboarding.submit') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ 
                        subdomain: subdomain,
                        operating_hours: operatingHours
                    })
                });
                
                if(res.ok) {
                    myModal.hide();
                    window.location.reload();
                } else {
                    const errorData = await res.json();
                    alert('Gagal menyimpan data: ' + (errorData.message || 'Cek kembali input Anda.'));
                    btnSubmit.disabled = false;
                    btnSubmit.innerText = 'Selesaikan Onboarding';
                }
            } catch(e) {
                alert('Terjadi kesalahan sistem.');
                btnSubmit.disabled = false;
                btnSubmit.innerText = 'Selesaikan Onboarding';
            }
        });
    });
    </script>
    @endif

    <style>
        .fw-black {
            font-weight: 900;
        }

        .rounded-4 {
            border-radius: 1.5rem !important;
        }

        .rounded-5 {
            border-radius: 2.5rem !important;
        }

        .btn-white {
            background-color: #fff;
            border-color: #fff;
        }

        .btn-white:hover {
            background-color: #f8f9fa;
            border-color: #f8f9fa;
        }

        .hover-scale:hover {
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }

        .hover-translate-y:hover {
            transform: translateY(-5px);
        }

        .backdrop-blur {
            backdrop-filter: blur(8px);
        }
    </style>
@endsection