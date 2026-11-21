<svg {{ $attributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 350">
    <rect id="background" width="100%" height="100%" fill="transparent" />
    {{-- Sombra de las letras --}}
    <text x="400" y="305" fill="rgba(0,0,0,0.25)" font-family="'Arial Black', Impact, sans-serif" font-size="280" font-weight="bold" letter-spacing="15" text-anchor="middle">SIA</text>
    {{-- Letras principales blancas --}}
    <text x="400" y="300" fill="#ffffff" stroke="#cbd5e1" stroke-width="3" font-family="'Arial Black', Impact, sans-serif" font-size="280" font-weight="bold" letter-spacing="15" text-anchor="middle">SIA</text>
    {{-- Birrete con borla dorada --}}
    <g transform="matrix(.7 0 0 .7 315 40)">
        <path fill="#64748b" d="M50 100q50 30 100 0v20q-50 30-100 0Z" />
        <path fill="#94a3b8" d="M50 100v20h100v-20Z" />
        <path fill="#cbd5e1" stroke="#475569" stroke-width="2" d="m100 60 90 30-90 30-90-30z" />
        <circle cx="100" cy="90" r="6" fill="#334155" />
        <path fill="gold" stroke="#daa520" stroke-width="1" d="m100 90 60 20q10 40 5 60h10l-15-60" />
        <circle cx="170" cy="175" r="8" fill="gold" />
    </g>
</svg>