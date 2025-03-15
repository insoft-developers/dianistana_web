@extends('frontend.master')
@section('content')
<style>
    body {
        font-family: "Roboto", Sans-serif !important;
    }
</style>



<!-- Hero section end -->
   <!-- Explore Cities Start-->
   <section class="explore-cities-section pt-[30px] pb-[120px] lg:py-[100px]">
    <div class="container">
        <div class="grid grid-cols-12">
            <div class="col-span-12">
                @if($message = Session::get('error'))
                <div class="alert alert-danger" role="alert">
                    <span onclick="tutup_alert()" class="btn-colse">x</span>
                    <?= $message ;?>
                </div>
                @endif
                <div class="mb-[30px] lg:mb-[60px] text-center">
                    
                    <h3 class="font-lora jarak30 custom-title">
                        
                    </h3>
                    <div class="jarak20"></div>
                </div>
                <div class="cities-slider">
                    <div class="swiper  -mx-[30px] -my-[60px] px-[30px] py-[60px]">
                        <div class="swiper-wrapper">
                            @foreach($banner as $b)
                            <div class="swiper-slide text-center">
                                <div class="relative group">
                                    <a href="{{ $b->link_terkait }}" target="_blank" class="block group-hover:shadow-[0_10px_15px_0px_rgba(0,0,0,0.1)] transition-all duration-300">
                                        <img src="{{ asset('template/images/banners') }}/{{ $b->image }}" class="banner-image w-full h-full block mx-auto rounded-[6px]" loading="lazy" alt="New York">
                                        <div class="bg-[rgb(255,253,252,0.9)] rounded-[6px] px-[5px] py-[15px] absolute group-hover:bottom-[25px] group-hover:opacity-100 bottom-[0px] opacity-0 left-[25px] right-[25px] transition-all duration-500">
                                            <span class="font-lora font-normal text-[18px] text-primary transition-all leading-none">{{ $b->title }}</span>
                                            <p class="font-light text-[14px] capitalize text-secondary transition-all leading-none"></p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            @endforeach


                            <!-- swiper-slide end-->
                        </div>
                        <!-- Add Pagination -->
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Explore Cities End-->



<!-- service Section Start-->

<section class="pt-[60px] pb-[120px] lg:py-[60px]">
    <div class="container">
        <div class="grid grid-cols-12">
            <div class="col-span-12">
                
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-[30px]">
            
            
            <div class="relative group">
                <a href="{{ url('frontend_booking') }}" class="block">
                    <img src="{{ asset('template/images/unit') }}/booking.png" class="menu-image" loading="lazy" width="270" height="290" alt="Booking">
                    <div class="menu-dashboard drop-shadow-[0px_2px_15px_rgba(0,0,0,0.1)] hover:drop-shadow-[0px_8px_20px_rgba(0,0,0,0.15)] bg-[#FFFDFC] rounded-[0px_0px_6px_6px] px-[25px] py-[25px]">
                        <h3 class="dashboard-title font-normal text-[24px] xl:text-lg text-primary group-hover:text-secondary transition-all mb-[5px]">Booking<span class="text-secondary group-hover:text-primary"></span> </h3>
                        <p class="font-light text-tiny">Reserve pools, tennis and basketball courts, and other communal spaces within the facility.</p>
                    </div>
                </a>
            </div>
             <div class="relative group">
                <a href="{{ url('riwayat') }}" class="block">
                    <img src="{{ asset('template/images/unit') }}/history-phone.png" class="menu-image" loading="lazy" width="270" height="290" alt="Booking">
                    <div class="menu-dashboard drop-shadow-[0px_2px_15px_rgba(0,0,0,0.1)] hover:drop-shadow-[0px_8px_20px_rgba(0,0,0,0.15)] bg-[#FFFDFC] rounded-[0px_0px_6px_6px] px-[25px] py-[25px]">
                        <h3 class="dashboard-title font-normal text-[24px] xl:text-lg text-primary group-hover:text-secondary transition-all mb-[5px]">History<span class="text-secondary group-hover:text-primary"></span> </h3>
                        <p class="font-light text-tiny">View your past bookings for facilities like pools, courts, and spaces, including dates and statuses.</p>
                    </div>
                </a>
            </div>
            <div class="relative group">
                <a href="{{ url('ticketing') }}" class="block">
                    <img src="{{ asset('template/images/unit') }}/ticketing.png" class="menu-image" loading="lazy" width="270" height="290" alt="Ticketing">
                    <div class="menu-dashboard drop-shadow-[0px_2px_15px_rgba(0,0,0,0.1)] hover:drop-shadow-[0px_8px_20px_rgba(0,0,0,0.15)] bg-[#FFFDFC] rounded-[0px_0px_6px_6px] px-[25px] py-[25px]">
                        <h3 class="dashboard-title font-normal text-[24px] xl:text-lg text-primary group-hover:text-secondary transition-all mb-[5px]">Ticketing<span class="text-secondary group-hover:text-primary"></span> </h3>
                        <p class="font-light text-tiny">Menu for service requests, upgrades, complaints, and direct communication with admin.</p>
                    </div>
                </a>
            </div>
            @if(Auth::user()->level == "user")
            <div class="relative group">
                <a href="{{ url('payment') }}" class="block">
                    <img src="{{ asset('template/images/unit') }}/payment.png" class="menu-image" loading="lazy" width="270" height="290" alt="Payment & Bill">
                    <div class="menu-dashboard drop-shadow-[0px_2px_15px_rgba(0,0,0,0.1)] hover:drop-shadow-[0px_8px_20px_rgba(0,0,0,0.15)] bg-[#FFFDFC] rounded-[0px_0px_6px_6px] px-[25px] py-[25px]">
                        <h3 class="dashboard-title font-normal text-[24px] xl:text-lg text-primary group-hover:text-secondary transition-all mb-[5px]">Payment & Bill<span class="text-secondary group-hover:text-primary"></span> </h3>
                        <p class="font-light text-tiny">System for handling all payments including monthly dues, routine charges, and other fees.</p>
                    </div>
                </a>
            </div>
            <div class="relative group">
                <a href="{{ url('user_data') }}" class="block">
                    <img src="{{ asset('template/images/unit') }}/datamenu.png" class="menu-image" loading="lazy" width="270" height="290" alt="Data User">
                    <div class="menu-dashboard drop-shadow-[0px_2px_15px_rgba(0,0,0,0.1)] hover:drop-shadow-[0px_8px_20px_rgba(0,0,0,0.15)] bg-[#FFFDFC] rounded-[0px_0px_6px_6px] px-[25px] py-[25px]">
                        <h3 class="dashboard-title font-normal text-[24px] xl:text-lg text-primary group-hover:text-secondary transition-all mb-[5px]">User Data<span class="text-secondary group-hover:text-primary"></span> </h3>
                        <p class="font-light text-tiny">Manage and review your property ownership details in the Dian Istana community.</p>
                    </div>
                </a>
            </div>
            @endif
            <div class="relative group">
                <a href="{{ url('notif_list') }}" class="block">
                    <img src="{{ asset('template/images/unit') }}/notif_menu.png" class="menu-image" loading="lazy" width="270" height="290" alt="Notification">
                    <div class="menu-dashboard drop-shadow-[0px_2px_15px_rgba(0,0,0,0.1)] hover:drop-shadow-[0px_8px_20px_rgba(0,0,0,0.15)] bg-[#FFFDFC] rounded-[0px_0px_6px_6px] px-[25px] py-[25px]">
                        <h3 class="dashboard-title font-normal text-[24px] xl:text-lg text-primary group-hover:text-secondary transition-all mb-[5px]">Notifications<span class="text-secondary group-hover:text-primary"></span> </h3>
                        <p class="font-light text-tiny">Get timely updates and news, ensuring you benefit directly from Dian Istana’s latest developments.</p>
                    </div>
                </a>
            </div>
            <div class="relative group">
                <a href="{{ url('frontend_setting') }}" class="block">
                    <img src="{{ asset('template/images/unit') }}/menu_profiles.png" class="menu-image" loading="lazy" width="270" height="290" alt="Mortgage">
                    <div class="menu-dashboard drop-shadow-[0px_2px_15px_rgba(0,0,0,0.1)] hover:drop-shadow-[0px_8px_20px_rgba(0,0,0,0.15)] bg-[#FFFDFC] rounded-[0px_0px_6px_6px] px-[25px] py-[25px]">
                        <h3 class="dashboard-title font-normal text-[24px] xl:text-lg text-primary group-hover:text-secondary transition-all mb-[5px]">Profile<span class="text-secondary group-hover:text-primary"></span> </h3>
                        <p class="font-light text-tiny">Easily update your personal details within the myDianIstana application interface. </p>
                    </div>
                </a>
            </div>
            <div class="relative group">
                <a href="{{ url('frontend_change_password') }}" class="block">
                    <img src="{{ asset('template/images/unit') }}/menu_password.png" class="menu-image" loading="lazy" width="270" height="290" alt="Mortgage">
                    <div class="menu-dashboard drop-shadow-[0px_2px_15px_rgba(0,0,0,0.1)] hover:drop-shadow-[0px_8px_20px_rgba(0,0,0,0.15)] bg-[#FFFDFC] rounded-[0px_0px_6px_6px] px-[25px] py-[25px]">
                        <h3 class="dashboard-title font-normal text-[24px] xl:text-lg text-primary group-hover:text-secondary transition-all mb-[5px]">Change Password<span class="text-secondary group-hover:text-primary"></span> </h3>
                        <p class="font-light text-tiny">Change your password securely to maintain access to the myDianIstana app.</p>
                    </div>
                </a>
            </div>
            
            
        </div>
    </div>
</section>

<!-- service Section End-->

@endsection