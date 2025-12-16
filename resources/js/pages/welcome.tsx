import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

import { useState } from 'react';

import FieoLogo from '@/components/FieoLogo';
import { ImageCubeCanvas } from '@/components/three-d/ImageCube';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    BarChart3,
    Briefcase,
    Cloud,
    Database,
    Edit3,
    Globe2,
    Layers,
    LayoutDashboard,
    Network,
    Server,
    Shield,
    ShieldCheck,
    Users,
} from 'lucide-react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    // Local light / dark theme for this page only
    const [theme, setTheme] = useState<'dark' | 'light'>('light');
    const isDark = theme === 'dark';

    return (
        <>
            <Head title="FIEO Dev Server">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                    rel="stylesheet"
                />
            </Head>

            <div
                className={[
                    'min-h-screen px-4 pt-4 pb-10 transition-colors duration-300 lg:px-10 lg:pt-6',
                    isDark
                        ? 'bg-gradient-to-b from-[#050816] via-[#050816] to-[#020617] text-slate-50'
                        : 'bg-gradient-to-b from-slate-50 via-white to-slate-100 text-slate-900',
                ].join(' ')}
            >
                {/* Top navigation */}
                <header
                    className={[
                        'mx-auto flex w-full max-w-6xl items-center justify-between rounded-full border px-4 py-2 backdrop-blur-lg transition-colors duration-300',
                        isDark
                            ? 'border-white/5 bg-black/30'
                            : 'border-slate-200 bg-white/70 shadow-sm',
                    ].join(' ')}
                >
                    <div className="flex items-center gap-2">
                        <div
                            className={[
                                'flex h-8 w-8 items-center justify-center rounded-lg',
                                // isDark
                                //     ? 'bg-gradient-to-br from-cyan-400 via-emerald-400 to-sky-500'
                                //     : 'bg-gradient-to-br from-sky-500 via-cyan-400 to-emerald-400',
                            ].join(' ')}
                        >
                            <span className="text-xs font-semibold tracking-[0.16em]">
                                <FieoLogo width={100} />
                            </span>
                        </div>
                        <div className="flex flex-col leading-tight">
                            <span
                                className={[
                                    'text-xs tracking-[0.2em] uppercase',
                                    isDark
                                        ? 'text-slate-400'
                                        : 'text-slate-500',
                                ].join(' ')}
                            >
                                FIEO
                            </span>
                            <span className="text-sm font-medium">
                                Development Server
                            </span>
                        </div>
                    </div>

                    <nav
                        className={[
                            'hidden items-center gap-6 text-xs font-medium md:flex',
                            isDark ? 'text-slate-300' : 'text-slate-600',
                        ].join(' ')}
                    >
                        <a href="#overview" className="hover:text-sky-400">
                            Overview
                        </a>
                        <a href="#ecosystem" className="hover:text-sky-400">
                            Ecosystem
                        </a>
                        <a href="#experiences" className="hover:text-sky-400">
                            Experiences
                        </a>
                        <a href="#preview" className="hover:text-sky-400">
                            Preview
                        </a>
                        <a href="#roadmap" className="hover:text-sky-400">
                            Roadmap
                        </a>
                    </nav>

                    <div className="flex items-center gap-2 text-xs">
                        {/* Theme toggle */}
                        <Button
                            size="icon"
                            variant="outline"
                            type="button"
                            onClick={() => setTheme(isDark ? 'light' : 'dark')}
                            className={[
                                'h-7 w-7 rounded-full border text-[11px] transition-colors duration-200',
                                isDark
                                    ? 'border-slate-500/60 bg-white/5 text-slate-100 hover:border-sky-400 hover:bg-sky-500/10'
                                    : 'border-slate-300 bg-white text-slate-700 hover:border-sky-400 hover:bg-sky-50',
                            ].join(' ')}
                        >
                            {isDark ? '☀' : '☾'}
                        </Button>

                        {auth.user ? (
                            <Link href={dashboard()}>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    className={[
                                        'border bg-white/5 text-xs transition-colors duration-200',
                                        isDark
                                            ? 'border-slate-500/60 text-slate-100 hover:border-sky-400 hover:bg-sky-500/10 hover:text-white'
                                            : 'border-slate-300 bg-white text-slate-700 hover:border-sky-400 hover:bg-sky-50',
                                    ].join(' ')}
                                >
                                    Go to dashboard
                                </Button>
                            </Link>
                        ) : (
                            <>
                                <a
                                    href="/admin"
                                    className={[
                                        'hidden rounded-full px-3 py-1 text-xs transition-colors duration-200 md:inline-block',
                                        isDark
                                            ? 'text-slate-300 hover:bg-white/5'
                                            : 'text-slate-600 hover:bg-slate-100',
                                    ].join(' ')}
                                >
                                    Admin panel
                                </a>
                                <a
                                    href="/employee"
                                    className={[
                                        'hidden rounded-full px-3 py-1 text-xs transition-colors duration-200 md:inline-block',
                                        isDark
                                            ? 'text-slate-300 hover:bg-white/5'
                                            : 'text-slate-600 hover:bg-slate-100',
                                    ].join(' ')}
                                >
                                    Employee panel
                                </a>
                                <Link href={login()}>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        className={[
                                            'text-xs transition-colors duration-200',
                                            isDark
                                                ? 'text-slate-200 hover:bg-white/5'
                                                : 'text-slate-700 hover:bg-slate-100',
                                        ].join(' ')}
                                    >
                                        Log in
                                    </Button>
                                </Link>
                                <Link href={register()}>
                                    <Button
                                        size="sm"
                                        className="bg-sky-500 text-xs text-slate-950 shadow-lg shadow-sky-500/30 hover:bg-sky-400"
                                    >
                                        Register
                                    </Button>
                                </Link>
                            </>
                        )}
                    </div>
                </header>

                {/* Main content */}
                <main className="mx-auto mt-8 flex w-full max-w-6xl flex-col gap-8 lg:mt-10 lg:flex-row">
                    {/* Hero + key information */}
                    <section
                        id="overview"
                        className={[
                            'flex flex-1 flex-col gap-6 rounded-3xl border p-6 shadow-[0_0_80px_rgba(56,189,248,0.25)] backdrop-blur-2xl transition-colors duration-300 lg:p-8',
                            isDark
                                ? 'border-white/10 bg-gradient-to-br from-slate-900/80 via-slate-900/60 to-slate-900/40'
                                : 'border-slate-200 bg-gradient-to-br from-white via-slate-50 to-slate-100 shadow-[0_18px_45px_rgba(15,23,42,0.12)]',
                        ].join(' ')}
                    >
                        <div
                            className={[
                                'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-medium tracking-[0.2em] uppercase',
                                isDark
                                    ? 'border-emerald-400/40 bg-emerald-500/10 text-emerald-200'
                                    : 'border-emerald-500/30 bg-emerald-50 text-emerald-700',
                            ].join(' ')}
                        >
                            <span
                                className={[
                                    'h-1.5 w-1.5 rounded-full shadow-[0_0_0_4px_rgba(52,211,153,0.4)]',
                                    isDark
                                        ? 'bg-emerald-400'
                                        : 'bg-emerald-500',
                                ].join(' ')}
                            />
                            Demo preview · New FIEO digital ecosystem
                        </div>

                        <div className="space-y-4">
                            <h5
                                className={[
                                    'text-xl font-semibold tracking-tight text-balance sm:text-2xl lg:text-3xl',
                                    isDark ? 'text-slate-50' : 'text-slate-900',
                                ].join(' ')}
                            >
                                Overview of one unified platform for Indian
                                Exporters, Overseas Buyers, MSMEs, FIEO Teams
                                and other stakeholders
                            </h5>
                            <p
                                className={[
                                    'max-w-xl text-sm leading-relaxed sm:text-[15px]',
                                    isDark
                                        ? 'text-slate-300'
                                        : 'text-slate-600',
                                ].join(' ')}
                            >
                                This preview home page demonstrates how the new
                                FIEO digital experience shall welcome members,
                                non-members, employees and partners into a
                                single, secure, unified ecosystem powered by
                                modern technologies
                            </p>

                            <div className="flex flex-wrap gap-2 text-[11px]">
                                <Badge
                                    variant="outline"
                                    className={[
                                        'border bg-sky-500/10',
                                        isDark
                                            ? 'border-sky-400/50 text-sky-100'
                                            : 'border-sky-500/50 text-sky-800',
                                    ].join(' ')}
                                >
                                    e-RCMC & FIEO Membership
                                </Badge>
                                <Badge
                                    variant="outline"
                                    className={[
                                        'border bg-blue-500/10',
                                        isDark
                                            ? 'border-blue-400/50 text-blue-100'
                                            : 'border-blue-500/50 text-blue-800',
                                    ].join(' ')}
                                >
                                    Certificate of Origin
                                </Badge>
                                <Badge
                                    variant="outline"
                                    className={[
                                        'border bg-emerald-500/10',
                                        isDark
                                            ? 'border-emerald-400/50 text-emerald-100'
                                            : 'border-emerald-500/50 text-emerald-800',
                                    ].join(' ')}
                                >
                                    Events & Delegations
                                </Badge>
                                <Badge
                                    variant="outline"
                                    className={[
                                        'border bg-violet-500/10',
                                        isDark
                                            ? 'border-violet-400/50 text-violet-100'
                                            : 'border-violet-500/50 text-violet-800',
                                    ].join(' ')}
                                >
                                    Policy & Trade Facilitation
                                </Badge>
                                <Badge
                                    variant="outline"
                                    className={[
                                        'border bg-red-500/10',
                                        isDark
                                            ? 'border-red-300/60 text-red-100'
                                            : 'border-red-400/70 text-red-800',
                                    ].join(' ')}
                                >
                                    Grievance Redressal
                                </Badge>
                                <Badge
                                    variant="outline"
                                    className={[
                                        'border bg-yellow-500/10',
                                        isDark
                                            ? 'border-yellow-300/60 text-yellow-100'
                                            : 'border-yellow-400/70 text-yellow-800',
                                    ].join(' ')}
                                >
                                    Expert Guidance & Support
                                </Badge>
                            </div>
                        </div>

                        <div className="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div className="flex flex-wrap gap-2">
                                <Link href={login()}>
                                    <Button className="bg-sky-500 text-slate-950 shadow-lg shadow-sky-500/30 hover:bg-sky-400">
                                        Explore Members Area
                                    </Button>
                                </Link>
                                <a href="/employee">
                                    <Button
                                        variant="outline"
                                        className={[
                                            'border text-xs transition-colors duration-200',
                                            isDark
                                                ? 'border-slate-500/70 bg-white/5 text-slate-100 hover:border-sky-400 hover:bg-sky-500/10'
                                                : 'border-slate-300 bg-white text-slate-700 hover:border-sky-400 hover:bg-sky-50',
                                        ].join(' ')}
                                    >
                                        Test Employee Console
                                    </Button>
                                </a>
                            </div>
                            <p
                                className={[
                                    'text-[11px]',
                                    isDark
                                        ? 'text-slate-400'
                                        : 'text-slate-500',
                                ].join(' ')}
                            >
                                Each stakeholder shall access their unique
                                dashboard with relevant information, in a
                                user-friendly environment
                            </p>
                        </div>

                        {/* Stats row */}
                        <Separator
                            className={[
                                'my-4',
                                isDark
                                    ? 'border-slate-700/60'
                                    : 'border-slate-200',
                            ].join(' ')}
                        />
                        <div className="grid grid-cols-2 gap-4 text-xs sm:grid-cols-4">
                            <div>
                                <p
                                    className={[
                                        'text-[11px] tracking-[0.18em] uppercase',
                                        isDark
                                            ? 'text-slate-400'
                                            : 'text-slate-500',
                                    ].join(' ')}
                                >
                                    FIEO Members Onboard
                                </p>
                                <p
                                    className={[
                                        'mt-1 text-lg font-semibold',
                                        isDark
                                            ? 'text-sky-300'
                                            : 'text-sky-600',
                                    ].join(' ')}
                                >
                                    38k+
                                </p>
                            </div>
                            <div>
                                <p
                                    className={[
                                        'text-[11px] tracking-[0.18em] uppercase',
                                        isDark
                                            ? 'text-slate-400'
                                            : 'text-slate-500',
                                    ].join(' ')}
                                >
                                    Events & Delegations
                                </p>
                                <p
                                    className={[
                                        'mt-1 text-lg font-semibold',
                                        isDark
                                            ? 'text-emerald-300'
                                            : 'text-emerald-600',
                                    ].join(' ')}
                                >
                                    500+
                                </p>
                            </div>
                            <div>
                                <p
                                    className={[
                                        'text-[11px] tracking-[0.18em] uppercase',
                                        isDark
                                            ? 'text-slate-400'
                                            : 'text-slate-500',
                                    ].join(' ')}
                                >
                                    Policy Updates
                                </p>
                                <p
                                    className={[
                                        'mt-1 text-lg font-semibold',
                                        isDark
                                            ? 'text-violet-300'
                                            : 'text-violet-600',
                                    ].join(' ')}
                                >
                                    Real-time
                                </p>
                            </div>
                            <div>
                                <p
                                    className={[
                                        'text-[11px] tracking-[0.18em] uppercase',
                                        isDark
                                            ? 'text-slate-400'
                                            : 'text-slate-500',
                                    ].join(' ')}
                                >
                                    Business Match Making
                                </p>
                                <p
                                    className={[
                                        'mt-1 text-lg font-semibold',
                                        isDark
                                            ? 'text-amber-200'
                                            : 'text-amber-600',
                                    ].join(' ')}
                                >
                                    AI Powered
                                </p>
                            </div>
                        </div>
                    </section>

                    {/* Logo / hero visual */}
                    <section className="flex flex-1 items-stretch">
                        <Card
                            className={[
                                'flex w-full flex-col justify-between rounded-3xl border p-0 text-slate-50 shadow-[0_0_60px_rgba(129,140,248,0.35)] backdrop-blur-2xl transition-colors duration-300',
                                isDark
                                    ? 'border-white/10 bg-gradient-to-br from-slate-900/70 via-slate-900/40 to-slate-900/20'
                                    : 'border-slate-200 bg-gradient-to-br from-white via-slate-50 to-slate-100 text-slate-900 shadow-[0_18px_45px_rgba(15,23,42,0.12)]',
                            ].join(' ')}
                        >
                            <CardHeader className="space-y-1 px-6 pt-6">
                                <CardTitle
                                    className={[
                                        'text-sm font-medium',
                                        isDark
                                            ? 'text-slate-200'
                                            : 'text-slate-800',
                                    ].join(' ')}
                                >
                                    Live Notifications & Announcements [FIEO
                                    Digital Notice Board]
                                </CardTitle>
                                <CardDescription
                                    className={[
                                        'text-xs',
                                        isDark
                                            ? 'text-slate-400'
                                            : 'text-slate-500',
                                    ].join(' ')}
                                >
                                    This panel may evolve into a live data
                                    visualisation, export heatmap, or a dynamic
                                    FIEO notice board showcasing real-time
                                    updates, announcements, and important
                                    notifications.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-1 items-stretch px-4 pt-3 pb-6">
                                <div
                                    className={[
                                        // full width, fixed height that scales nicely on desktop
                                        'relative flex h-[260px] w-full items-center justify-center overflow-hidden rounded-2xl md:h-[320px]',
                                        isDark
                                            ? 'bg-slate-950'
                                            : 'bg-slate-100',
                                    ].join(' ')}
                                >
                                    <ImageCubeCanvas imageUrl="/images/fieo-logo.png" />
                                </div>
                            </CardContent>
                            {/* <CardContent className="flex flex-1 items-stretch px-4 pt-3 pb-6">
                                <div
                                    className={[
                                        'relative flex h-[260px] w-full items-center justify-center overflow-hidden rounded-2xl md:h-[320px]',
                                        isDark
                                            ? 'bg-slate-950'
                                            : 'bg-slate-100',
                                    ].join(' ')}
                                >
                                    <TextCubeCanvas
                                        text="FIEO"
                                        backgroundColor={
                                            isDark ? '#020617' : '#e5f2ff'
                                        }
                                        textColor={
                                            isDark ? '#38bdf8' : '#0369a1'
                                        }
                                    />
                                </div>
                            </CardContent> */}
                            {/* <CardContent className="flex flex-1 items-stretch px-4 pt-3 pb-6">
                                <div
                                    className={[
                                        'relative flex h-[320px] w-full items-center justify-center overflow-hidden rounded-2xl md:h-[420px]',
                                        isDark
                                            ? 'bg-slate-950'
                                            : 'bg-slate-100',
                                    ].join(' ')}
                                >
                                    <GlobeCanvas
                                        textureUrl="/images/earth-map.png" // use .png if that’s what’s on disk
                                        globeTint={
                                            isDark ? '#38bdf8' : '#0ea5e9'
                                        }
                                        atmosphereColor={
                                            isDark ? '#60a5fa' : '#93c5fd'
                                        }
                                        scale={0.7} // enlarge or shrink here
                                        spinSpeed={0.03} // adjust rotation speed
                                    />
                                </div>
                            </CardContent> */}
                        </Card>
                    </section>
                </main>

                {/* Ecosystem section */}
                <section
                    id="ecosystem"
                    className="mx-auto mt-10 w-full max-w-6xl"
                >
                    <Card
                        className={[
                            'border backdrop-blur-xl transition-colors duration-300',
                            isDark
                                ? 'border-white/10 bg-slate-900/60 text-slate-100'
                                : 'border-slate-200 bg-white text-slate-800 shadow-sm',
                        ].join(' ')}
                    >
                        <CardHeader>
                            <CardTitle className="text-sm">
                                FIEO new digital ecosystem built on modern tech
                                stack
                            </CardTitle>
                            <CardDescription
                                className={[
                                    'text-xs',
                                    isDark
                                        ? 'text-slate-400'
                                        : 'text-slate-500',
                                ].join(' ')}
                            >
                                A modern, API-driven architecture using Laravel
                                on the backend and React JS on the frontend to
                                power the next generation of FIEO&apos;s digital
                                services.
                            </CardDescription>
                        </CardHeader>

                        <CardContent
                            className={[
                                'grid gap-6 text-xs md:grid-cols-2',
                                isDark ? 'text-slate-300' : 'text-slate-700',
                            ].join(' ')}
                        >
                            {/* Backend and core services */}
                            <div className="space-y-3">
                                <div className="flex items-start gap-2">
                                    <Server className="mt-0.5 h-4 w-4 shrink-0" />
                                    <div>
                                        <p className="text-[0.8rem] font-medium">
                                            Laravel 12 API backend
                                        </p>
                                        <p className="mt-0.5 text-[0.74rem]">
                                            RESTful JSON APIs powering
                                            membership, events, HR workflows and
                                            integrations with DGFT, payment
                                            gateways and partner platforms.
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-2">
                                    <Database className="mt-0.5 h-4 w-4 shrink-0" />
                                    <div>
                                        <p className="text-[0.8rem] font-medium">
                                            Robust data layer
                                        </p>
                                        <p className="mt-0.5 text-[0.74rem]">
                                            Structured schemas on relational
                                            databases with audit fields,
                                            indexing and secure access for
                                            membership, events, policy and
                                            analytics data.
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-2">
                                    <ShieldCheck className="mt-0.5 h-4 w-4 shrink-0" />
                                    <div>
                                        <p className="text-[0.8rem] font-medium">
                                            Security and governance
                                        </p>
                                        <p className="mt-0.5 text-[0.74rem]">
                                            Role and rules based access, API
                                            authentication, logging and audit
                                            trails aligned with FIEO&apos;s
                                            internal processes.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Frontend and experience layer */}
                            <div className="space-y-3">
                                <div className="flex items-start gap-2">
                                    <LayoutDashboard className="mt-0.5 h-4 w-4 shrink-0" />
                                    <div>
                                        <p className="text-[0.8rem] font-medium">
                                            React JS frontend
                                        </p>
                                        <p className="mt-0.5 text-[0.74rem]">
                                            A React and TypeScript based
                                            interface with Tailwind CSS and
                                            shadcn/ui delivering a fast,
                                            responsive experience for exporters
                                            and employees.
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-2">
                                    <Cloud className="mt-0.5 h-4 w-4 shrink-0" />
                                    <div>
                                        <p className="text-[0.8rem] font-medium">
                                            Component-driven design
                                        </p>
                                        <p className="mt-0.5 text-[0.74rem]">
                                            Reusable UI components for
                                            dashboards, forms, notices and
                                            visualisations, enabling consistent
                                            layouts across the FIEO website and
                                            portals.
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-2">
                                    <ShieldCheck className="mt-0.5 h-4 w-4 shrink-0" />
                                    <div>
                                        <p className="text-[0.8rem] font-medium">
                                            Ready for future expansion
                                        </p>
                                        <p className="mt-0.5 text-[0.74rem]">
                                            Designed to extend into mobile apps,
                                            exporter dashboards and analytics
                                            modules without changing the core
                                            stack.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </section>

                {/* Experiences section */}
                <section
                    id="experiences"
                    className="mx-auto mt-10 grid w-full max-w-6xl gap-6 lg:grid-cols-3"
                >
                    {/* Exporter experience */}
                    <Card
                        className={[
                            'border backdrop-blur-xl transition-colors duration-300',
                            isDark
                                ? 'border-white/10 bg-slate-900/60 text-slate-100'
                                : 'border-slate-200 bg-white text-slate-800 shadow-sm',
                        ].join(' ')}
                    >
                        <CardHeader className="flex flex-row items-center gap-3">
                            <Users className="h-5 w-5 text-blue-500" />
                            <div>
                                <CardTitle className="text-sm">
                                    Exporter experience
                                </CardTitle>
                                <CardDescription
                                    className={[
                                        'text-xs',
                                        isDark
                                            ? 'text-slate-400'
                                            : 'text-slate-500',
                                    ].join(' ')}
                                >
                                    A unified dashboard for membership, events,
                                    networking and policy updates.
                                </CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent
                            className={[
                                'text-xs',
                                isDark ? 'text-slate-300' : 'text-slate-700',
                            ].join(' ')}
                        >
                            <ul className="space-y-2">
                                <li className="flex items-start gap-2">
                                    <Layers className="mt-[2px] h-4 w-4" />
                                    Single dashboard for managing Profile,
                                    Membership and Participation in various FIEO
                                    Activities
                                </li>
                                <li className="flex items-start gap-2">
                                    <Network className="mt-[2px] h-4 w-4" />
                                    Smart and seamless access to all FIEO
                                    services and resources
                                </li>
                                <li className="flex items-start gap-2">
                                    <Globe2 className="mt-[2px] h-4 w-4" />
                                    Guided journeys for new exporters, MSMEs and
                                    women-led businesses
                                </li>
                            </ul>
                        </CardContent>
                    </Card>

                    {/* Employee experience */}
                    <Card
                        className={[
                            'border backdrop-blur-xl transition-colors duration-300',
                            isDark
                                ? 'border-white/10 bg-slate-900/60 text-slate-100'
                                : 'border-slate-200 bg-white text-slate-800 shadow-sm',
                        ].join(' ')}
                    >
                        <CardHeader className="flex flex-row items-center gap-3">
                            <Briefcase className="h-5 w-5 text-emerald-500" />
                            <div>
                                <CardTitle className="text-sm">
                                    Employee experience
                                </CardTitle>
                                <CardDescription
                                    className={[
                                        'text-xs',
                                        isDark
                                            ? 'text-slate-400'
                                            : 'text-slate-500',
                                    ].join(' ')}
                                >
                                    New rich content management capabilities for
                                    FIEO Employees
                                </CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent
                            className={[
                                'text-xs',
                                isDark ? 'text-slate-300' : 'text-slate-700',
                            ].join(' ')}
                        >
                            <ul className="space-y-2">
                                <li className="flex items-start gap-2">
                                    <Edit3 className="mt-[2px] h-4 w-4" />
                                    Completely redesigned content management
                                    interface to empower FIEO teams
                                </li>
                                <li className="flex items-start gap-2">
                                    <Briefcase className="mt-[2px] h-4 w-4" />
                                    Intuitive dashboard for employees to create,
                                    edit, and publish web content effortlessly
                                </li>
                                <li className="flex items-start gap-2">
                                    <Shield className="mt-[2px] h-4 w-4" />
                                    Role & Rules based access to tools and
                                    resources
                                </li>
                            </ul>
                        </CardContent>
                    </Card>

                    {/* Policy and analytics */}
                    <Card
                        className={[
                            'border backdrop-blur-xl transition-colors duration-300',
                            isDark
                                ? 'border-white/10 bg-slate-900/60 text-slate-100'
                                : 'border-slate-200 bg-white text-slate-800 shadow-sm',
                        ].join(' ')}
                    >
                        <CardHeader className="flex flex-row items-center gap-3">
                            <BarChart3 className="h-5 w-5 text-orange-500" />
                            <div>
                                <CardTitle className="text-sm">
                                    Policy and analytics
                                </CardTitle>
                                <CardDescription
                                    className={[
                                        'text-xs',
                                        isDark
                                            ? 'text-slate-400'
                                            : 'text-slate-500',
                                    ].join(' ')}
                                >
                                    Real-time insights to support DGFT,
                                    ministries and sector councils.
                                </CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent
                            className={[
                                'text-xs',
                                isDark ? 'text-slate-300' : 'text-slate-700',
                            ].join(' ')}
                        >
                            <ul className="space-y-2">
                                <li className="flex items-start gap-2">
                                    <BarChart3 className="mt-[2px] h-4 w-4" />
                                    Dashboard of exports, sectors and regions
                                </li>
                                <li className="flex items-start gap-2">
                                    <Globe2 className="mt-[2px] h-4 w-4" />
                                    Deep links to trade facilitation initiatives
                                </li>
                                <li className="flex items-start gap-2">
                                    <Users className="mt-[2px] h-4 w-4" />
                                    Structured feedback loops from exporters to
                                    policy teams
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </section>

                {/* Journey section continues*/}
                <section
                    id="journey"
                    className="mx-auto mt-10 w-full max-w-6xl"
                >
                    <Tabs
                        defaultValue="exporters"
                        className={[
                            'rounded-3xl border p-4 backdrop-blur-xl transition-colors duration-300 lg:p-6',
                            isDark
                                ? 'border-white/10 bg-slate-950/40'
                                : 'border-slate-200 bg-white shadow-sm',
                        ].join(' ')}
                    >
                        <div className="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                            <div>
                                <p
                                    className={[
                                        'text-xs font-medium tracking-[0.2em] uppercase',
                                        isDark
                                            ? 'text-slate-400'
                                            : 'text-slate-500',
                                    ].join(' ')}
                                >
                                    Preview journeys
                                </p>
                                <h2
                                    className={[
                                        'mt-1 text-lg font-semibold',
                                        isDark
                                            ? 'text-slate-50'
                                            : 'text-slate-900',
                                    ].join(' ')}
                                >
                                    How different stakeholders will experience
                                    the new FIEO portal
                                </h2>
                            </div>
                            <TabsList
                                className={[
                                    'transition-colors duration-200',
                                    isDark ? 'bg-slate-900/60' : 'bg-slate-100',
                                ].join(' ')}
                            >
                                <TabsTrigger
                                    value="exporters"
                                    className="text-xs"
                                >
                                    Exporters
                                </TabsTrigger>
                                <TabsTrigger
                                    value="employees"
                                    className="text-xs"
                                >
                                    Employees
                                </TabsTrigger>
                                <TabsTrigger
                                    value="partners"
                                    className="text-xs"
                                >
                                    Partners
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <Separator
                            className={[
                                'my-4',
                                isDark
                                    ? 'border-slate-800'
                                    : 'border-slate-200',
                            ].join(' ')}
                        />

                        <TabsContent
                            value="exporters"
                            className={[
                                'text-xs lg:text-[13px]',
                                isDark ? 'text-slate-300' : 'text-slate-700',
                            ].join(' ')}
                        >
                            <ol className="grid gap-4 sm:grid-cols-3">
                                <li
                                    className={[
                                        'rounded-2xl border p-4',
                                        isDark
                                            ? 'border-sky-500/40 bg-sky-500/5'
                                            : 'border-sky-300/60 bg-sky-50',
                                    ].join(' ')}
                                >
                                    <p
                                        className={[
                                            'text-[11px] tracking-[0.18em] uppercase',
                                            isDark
                                                ? 'text-sky-300'
                                                : 'text-sky-600',
                                        ].join(' ')}
                                    >
                                        Step 1
                                    </p>
                                    <p className="mt-2 font-medium">
                                        Guided onboarding
                                    </p>
                                    <p className="mt-1">
                                        Import IEC and DGFT data, verify RCMC
                                        and create a unified exporter profile.
                                    </p>
                                </li>
                                <li
                                    className={[
                                        'rounded-2xl border p-4',
                                        isDark
                                            ? 'border-sky-500/20 bg-sky-500/5'
                                            : 'border-sky-300/60 bg-sky-50',
                                    ].join(' ')}
                                >
                                    <p
                                        className={[
                                            'text-[11px] tracking-[0.18em] uppercase',
                                            isDark
                                                ? 'text-sky-200'
                                                : 'text-sky-600',
                                        ].join(' ')}
                                    >
                                        Step 2
                                    </p>
                                    <p className="mt-2 font-medium">
                                        Discover opportunities
                                    </p>
                                    <p className="mt-1">
                                        Personalised list of events, delegations
                                        and schemes based on sector and region.
                                    </p>
                                </li>
                                <li
                                    className={[
                                        'rounded-2xl border p-4',
                                        isDark
                                            ? 'border-sky-500/20 bg-sky-500/5'
                                            : 'border-sky-300/60 bg-sky-50',
                                    ].join(' ')}
                                >
                                    <p
                                        className={[
                                            'text-[11px] tracking-[0.18em] uppercase',
                                            isDark
                                                ? 'text-sky-200'
                                                : 'text-sky-600',
                                        ].join(' ')}
                                    >
                                        Step 3
                                    </p>
                                    <p className="mt-2 font-medium">
                                        Track everything in one place
                                    </p>
                                    <p className="mt-1">
                                        Registrations, payments, approvals and
                                        certificates, all in a single timeline.
                                    </p>
                                </li>
                            </ol>
                        </TabsContent>

                        <TabsContent
                            value="employees"
                            className={[
                                'text-xs lg:text-[13px]',
                                isDark ? 'text-slate-300' : 'text-slate-700',
                            ].join(' ')}
                        >
                            <div className="grid gap-4 sm:grid-cols-3">
                                <div
                                    className={[
                                        'rounded-2xl border p-4',
                                        isDark
                                            ? 'border-violet-500/40 bg-violet-500/5'
                                            : 'border-violet-300/60 bg-violet-50',
                                    ].join(' ')}
                                >
                                    Streamlined event creation and approvals.
                                </div>
                                <div
                                    className={[
                                        'rounded-2xl border p-4',
                                        isDark
                                            ? 'border-violet-500/30 bg-violet-500/5'
                                            : 'border-violet-300/60 bg-violet-50',
                                    ].join(' ')}
                                >
                                    Integrated TA/DA, tour claims and travel
                                    policies.
                                </div>
                                <div
                                    className={[
                                        'rounded-2xl border p-4',
                                        isDark
                                            ? 'border-violet-500/30 bg-violet-500/5'
                                            : 'border-violet-300/60 bg-violet-50',
                                    ].join(' ')}
                                >
                                    Role-aware dashboards for regions, MRD and
                                    policy divisions.
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent
                            value="partners"
                            className={[
                                'text-xs lg:text-[13px]',
                                isDark ? 'text-slate-300' : 'text-slate-700',
                            ].join(' ')}
                        >
                            <div className="grid gap-4 sm:grid-cols-3">
                                <div
                                    className={[
                                        'rounded-2xl border p-4',
                                        isDark
                                            ? 'border-emerald-500/40 bg-emerald-500/5'
                                            : 'border-emerald-300/60 bg-emerald-50',
                                    ].join(' ')}
                                >
                                    Embed payment gateways, airport services and
                                    other APIs behind a consistent FIEO layer.
                                </div>
                                <div
                                    className={[
                                        'rounded-2xl border p-4',
                                        isDark
                                            ? 'border-emerald-500/30 bg-emerald-500/5'
                                            : 'border-emerald-300/60 bg-emerald-50',
                                    ].join(' ')}
                                >
                                    Dedicated partner views for NDML, Adani and
                                    other service providers.
                                </div>
                                <div
                                    className={[
                                        'rounded-2xl border p-4',
                                        isDark
                                            ? 'border-emerald-500/30 bg-emerald-500/5'
                                            : 'border-emerald-300/60 bg-emerald-50',
                                    ].join(' ')}
                                >
                                    Data sharing built on explicit consent and
                                    strong information security.
                                </div>
                            </div>
                        </TabsContent>
                    </Tabs>
                </section>

                {/* Events & updates section */}
                <section
                    id="updates"
                    className="mx-auto mt-10 w-full max-w-6xl space-y-8"
                >
                    {/* Row 1: Events + DG & CEO highlight */}
                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* Upcoming events (Domestic / Overseas) */}
                        <Card
                            className={[
                                'border backdrop-blur-xl transition-colors duration-300 lg:col-span-2',
                                isDark
                                    ? 'border-white/10 bg-slate-950/60 text-slate-100'
                                    : 'border-slate-200 bg-white text-slate-800 shadow-sm',
                            ].join(' ')}
                        >
                            <CardHeader className="space-y-2">
                                <div className="flex items-center justify-between gap-2">
                                    <div>
                                        <CardTitle className="text-sm">
                                            Upcoming events
                                        </CardTitle>
                                        <CardDescription
                                            className={
                                                isDark
                                                    ? 'text-xs text-slate-400'
                                                    : 'text-xs text-slate-500'
                                            }
                                        >
                                            A preview of how domestic and
                                            overseas events will appear on the
                                            redesigned home page.
                                        </CardDescription>
                                    </div>
                                    <span
                                        className={[
                                            'rounded-full px-3 py-1 text-[10px] font-medium tracking-[0.16em] uppercase',
                                            isDark
                                                ? 'border border-sky-500/40 bg-sky-500/10 text-sky-200'
                                                : 'border border-sky-300 bg-sky-50 text-sky-700',
                                        ].join(' ')}
                                    >
                                        Live calendar preview
                                    </span>
                                </div>
                            </CardHeader>
                            <CardContent className="pt-0">
                                <Tabs
                                    defaultValue="domestic"
                                    className="space-y-4"
                                >
                                    <TabsList
                                        className={
                                            isDark
                                                ? 'bg-slate-900/60'
                                                : 'bg-slate-100'
                                        }
                                    >
                                        <TabsTrigger
                                            value="domestic"
                                            className="text-xs"
                                        >
                                            Domestic events
                                        </TabsTrigger>
                                        <TabsTrigger
                                            value="overseas"
                                            className="text-xs"
                                        >
                                            Overseas events
                                        </TabsTrigger>
                                    </TabsList>

                                    {/* Domestic events */}
                                    <TabsContent value="domestic">
                                        <ul className="space-y-3 text-xs">
                                            <li
                                                className={[
                                                    'flex items-start justify-between gap-3 rounded-2xl border px-3 py-3',
                                                    isDark
                                                        ? 'border-slate-700 bg-slate-900/70'
                                                        : 'border-slate-200 bg-slate-50',
                                                ].join(' ')}
                                            >
                                                <div>
                                                    <p
                                                        className={
                                                            isDark
                                                                ? 'text-[11px] tracking-[0.18em] text-slate-400 uppercase'
                                                                : 'text-[11px] tracking-[0.18em] text-slate-500 uppercase'
                                                        }
                                                    >
                                                        12 Feb · New Delhi
                                                    </p>
                                                    <p className="mt-1 text-[13px] font-medium">
                                                        National Exporters’
                                                        Outreach – Northern
                                                        Region
                                                    </p>
                                                    <p
                                                        className={
                                                            isDark
                                                                ? 'mt-1 text-slate-300'
                                                                : 'mt-1 text-slate-600'
                                                        }
                                                    >
                                                        Sector-agnostic capacity
                                                        building programme with
                                                        focus on MSME exporters.
                                                    </p>
                                                </div>
                                                <span
                                                    className={[
                                                        'mt-1 inline-flex items-center rounded-full px-2 py-1 text-[10px]',
                                                        isDark
                                                            ? 'border border-emerald-500/40 bg-emerald-500/10 text-emerald-200'
                                                            : 'border border-emerald-300 bg-emerald-50 text-emerald-700',
                                                    ].join(' ')}
                                                >
                                                    Domestic
                                                </span>
                                            </li>

                                            <li
                                                className={[
                                                    'flex items-start justify-between gap-3 rounded-2xl border px-3 py-3',
                                                    isDark
                                                        ? 'border-slate-700 bg-slate-900/70'
                                                        : 'border-slate-200 bg-slate-50',
                                                ].join(' ')}
                                            >
                                                <div>
                                                    <p
                                                        className={
                                                            isDark
                                                                ? 'text-[11px] tracking-[0.18em] text-slate-400 uppercase'
                                                                : 'text-[11px] tracking-[0.18em] text-slate-500 uppercase'
                                                        }
                                                    >
                                                        20 Feb · Mumbai
                                                    </p>
                                                    <p className="mt-1 text-[13px] font-medium">
                                                        India Services Export
                                                        Conclave
                                                    </p>
                                                    <p
                                                        className={
                                                            isDark
                                                                ? 'mt-1 text-slate-300'
                                                                : 'mt-1 text-slate-600'
                                                        }
                                                    >
                                                        Focus on IT, ITeS,
                                                        healthcare and
                                                        professional services
                                                        exporters.
                                                    </p>
                                                </div>
                                                <span
                                                    className={[
                                                        'mt-1 inline-flex items-center rounded-full px-2 py-1 text-[10px]',
                                                        isDark
                                                            ? 'border border-emerald-500/40 bg-emerald-500/10 text-emerald-200'
                                                            : 'border border-emerald-300 bg-emerald-50 text-emerald-700',
                                                    ].join(' ')}
                                                >
                                                    Domestic
                                                </span>
                                            </li>
                                        </ul>
                                    </TabsContent>

                                    {/* Overseas events */}
                                    <TabsContent value="overseas">
                                        <ul className="space-y-3 text-xs">
                                            <li
                                                className={[
                                                    'flex items-start justify-between gap-3 rounded-2xl border px-3 py-3',
                                                    isDark
                                                        ? 'border-slate-700 bg-slate-900/70'
                                                        : 'border-slate-200 bg-slate-50',
                                                ].join(' ')}
                                            >
                                                <div>
                                                    <p
                                                        className={
                                                            isDark
                                                                ? 'text-[11px] tracking-[0.18em] text-slate-400 uppercase'
                                                                : 'text-[11px] tracking-[0.18em] text-slate-500 uppercase'
                                                        }
                                                    >
                                                        05 Mar · Dubai
                                                    </p>
                                                    <p className="mt-1 text-[13px] font-medium">
                                                        India Pavilion – Global
                                                        Sourcing Expo
                                                    </p>
                                                    <p
                                                        className={
                                                            isDark
                                                                ? 'mt-1 text-slate-300'
                                                                : 'mt-1 text-slate-600'
                                                        }
                                                    >
                                                        Multi-product B2B
                                                        meetings and sectoral
                                                        sessions with overseas
                                                        buyers.
                                                    </p>
                                                </div>
                                                <span
                                                    className={[
                                                        'mt-1 inline-flex items-center rounded-full px-2 py-1 text-[10px]',
                                                        isDark
                                                            ? 'border border-sky-500/40 bg-sky-500/10 text-sky-200'
                                                            : 'border border-sky-300 bg-sky-50 text-sky-700',
                                                    ].join(' ')}
                                                >
                                                    Overseas
                                                </span>
                                            </li>

                                            <li
                                                className={[
                                                    'flex items-start justify-between gap-3 rounded-2xl border px-3 py-3',
                                                    isDark
                                                        ? 'border-slate-700 bg-slate-900/70'
                                                        : 'border-slate-200 bg-slate-50',
                                                ].join(' ')}
                                            >
                                                <div>
                                                    <p
                                                        className={
                                                            isDark
                                                                ? 'text-[11px] tracking-[0.18em] text-slate-400 uppercase'
                                                                : 'text-[11px] tracking-[0.18em] text-slate-500 uppercase'
                                                        }
                                                    >
                                                        18 Mar · Frankfurt
                                                    </p>
                                                    <p className="mt-1 text-[13px] font-medium">
                                                        India–EU Trade &
                                                        Investment Forum
                                                    </p>
                                                    <p
                                                        className={
                                                            isDark
                                                                ? 'mt-1 text-slate-300'
                                                                : 'mt-1 text-slate-600'
                                                        }
                                                    >
                                                        High-level business
                                                        interactions, thematic
                                                        sessions and networking.
                                                    </p>
                                                </div>
                                                <span
                                                    className={[
                                                        'mt-1 inline-flex items-center rounded-full px-2 py-1 text-[10px]',
                                                        isDark
                                                            ? 'border border-sky-500/40 bg-sky-500/10 text-sky-200'
                                                            : 'border border-sky-300 bg-sky-50 text-sky-700',
                                                    ].join(' ')}
                                                >
                                                    Overseas
                                                </span>
                                            </li>
                                        </ul>
                                    </TabsContent>
                                </Tabs>

                                <div className="mt-4 flex items-center justify-between text-[11px]">
                                    <p
                                        className={
                                            isDark
                                                ? 'text-slate-400'
                                                : 'text-slate-500'
                                        }
                                    >
                                        Events will be pulled in real time from
                                        the new event module with filters for
                                        region, sector and format.
                                    </p>
                                    <a
                                        href="#"
                                        className={
                                            isDark
                                                ? 'text-sky-300 hover:text-sky-200'
                                                : 'text-sky-600 hover:text-sky-500'
                                        }
                                    >
                                        View full events calendar →
                                    </a>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Interactive meet with DG & CEO */}
                        <Card
                            className={[
                                'flex flex-col justify-between border backdrop-blur-xl transition-colors duration-300',
                                isDark
                                    ? 'border-amber-500/40 bg-gradient-to-br from-amber-900/60 via-slate-950 to-slate-950 text-amber-50'
                                    : 'border-amber-300/80 bg-gradient-to-br from-amber-50 via-white to-amber-50 text-amber-900 shadow-sm',
                            ].join(' ')}
                        >
                            <CardHeader className="space-y-2">
                                <CardTitle className="text-sm">
                                    Interactive meet with DG & CEO
                                </CardTitle>
                                <CardDescription
                                    className={
                                        isDark
                                            ? 'text-xs text-amber-100/80'
                                            : 'text-xs text-amber-800/80'
                                    }
                                >
                                    Wednesday · 3:00 PM (virtual + hybrid
                                    format)
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4 text-xs">
                                <p>
                                    Quarterly interactive town-hall where
                                    exporters can share feedback, raise policy
                                    issues and seek guidance directly from FIEO
                                    leadership.
                                </p>
                                <ul className="space-y-1">
                                    <li>
                                        • Curated questions from members across
                                        regions
                                    </li>
                                    <li>
                                        • Live poll on trade challenges and
                                        opportunities
                                    </li>
                                    <li>
                                        • Follow-up action tracker from the
                                        meeting
                                    </li>
                                </ul>
                                <Button className="mt-2 w-full bg-amber-400 text-amber-950 hover:bg-amber-300">
                                    Register interest
                                </Button>
                                <p
                                    className={
                                        isDark
                                            ? 'text-[11px] text-amber-100/80'
                                            : 'text-[11px] text-amber-800/80'
                                    }
                                >
                                    In the final implementation, this card will
                                    be driven by the events module and will
                                    appear only when such sessions are
                                    scheduled.
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Row 2: President, Press, Publications */}
                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* President's desk */}
                        <Card
                            className={[
                                'border backdrop-blur-xl transition-colors duration-300',
                                isDark
                                    ? 'border-white/10 bg-slate-950/60 text-slate-100'
                                    : 'border-slate-200 bg-white text-slate-800 shadow-sm',
                            ].join(' ')}
                        >
                            <CardHeader className="space-y-2">
                                <CardTitle className="text-sm">
                                    From the President&apos;s desk
                                </CardTitle>
                                <CardDescription
                                    className={
                                        isDark
                                            ? 'text-xs text-slate-400'
                                            : 'text-xs text-slate-500'
                                    }
                                >
                                    Monthly note highlighting key themes,
                                    opportunities and priorities for Indian
                                    exporters.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3 text-xs">
                                <div className="flex items-center gap-3">
                                    <div className="h-9 w-9 rounded-full bg-gradient-to-br from-sky-500 via-emerald-400 to-amber-300" />
                                    <div>
                                        <p className="text-[11px] tracking-[0.18em] text-sky-500 uppercase">
                                            January edition
                                        </p>
                                        <p
                                            className={
                                                isDark
                                                    ? 'text-[13px] font-medium text-slate-100'
                                                    : 'text-[13px] font-medium text-slate-900'
                                            }
                                        >
                                            Resilience, diversification and new
                                            growth geographies
                                        </p>
                                    </div>
                                </div>
                                <p
                                    className={
                                        isDark
                                            ? 'text-slate-300'
                                            : 'text-slate-700'
                                    }
                                >
                                    This section will carry a short excerpt of
                                    the monthly note with a link to the full
                                    article, including charts or featured
                                    initiatives where relevant.
                                </p>
                                <a
                                    href="#"
                                    className={
                                        isDark
                                            ? 'text-sky-300 hover:text-sky-200'
                                            : 'text-sky-600 hover:text-sky-500'
                                    }
                                >
                                    Read full note →
                                </a>
                            </CardContent>
                        </Card>

                        {/* Press releases */}
                        <Card
                            className={[
                                'border backdrop-blur-xl transition-colors duration-300',
                                isDark
                                    ? 'border-white/10 bg-slate-950/60 text-slate-100'
                                    : 'border-slate-200 bg-white text-slate-800 shadow-sm',
                            ].join(' ')}
                        >
                            <CardHeader className="space-y-2">
                                <CardTitle className="text-sm">
                                    Press releases
                                </CardTitle>
                                <CardDescription
                                    className={
                                        isDark
                                            ? 'text-xs text-slate-400'
                                            : 'text-xs text-slate-500'
                                    }
                                >
                                    Snapshot of the latest press notes and media
                                    statements issued by FIEO.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3 text-xs">
                                <ul className="space-y-2">
                                    <li>
                                        <p className="text-[11px] tracking-[0.18em] text-slate-400 uppercase">
                                            08 Jan · Policy
                                        </p>
                                        <p className="text-[13px] font-medium">
                                            FIEO welcomes measures to facilitate
                                            export credit for MSMEs
                                        </p>
                                    </li>
                                    <li>
                                        <p className="text-[11px] tracking-[0.18em] text-slate-400 uppercase">
                                            05 Jan · Trade data
                                        </p>
                                        <p className="text-[13px] font-medium">
                                            Commentary on India&apos;s
                                            merchandise and services export
                                            performance
                                        </p>
                                    </li>
                                    <li>
                                        <p className="text-[11px] tracking-[0.18em] text-slate-400 uppercase">
                                            30 Dec · Outreach
                                        </p>
                                        <p className="text-[13px] font-medium">
                                            FIEO launches new digital helpdesk
                                            for exporters
                                        </p>
                                    </li>
                                </ul>
                                <a
                                    href="#"
                                    className={
                                        isDark
                                            ? 'text-sky-300 hover:text-sky-200'
                                            : 'text-sky-600 hover:text-sky-500'
                                    }
                                >
                                    View all press releases →
                                </a>
                            </CardContent>
                        </Card>

                        {/* Latest publications */}
                        <Card
                            className={[
                                'border backdrop-blur-xl transition-colors duration-300',
                                isDark
                                    ? 'border-white/10 bg-slate-950/60 text-slate-100'
                                    : 'border-slate-200 bg-white text-slate-800 shadow-sm',
                            ].join(' ')}
                        >
                            <CardHeader className="space-y-2">
                                <CardTitle className="text-sm">
                                    Latest publications
                                </CardTitle>
                                <CardDescription
                                    className={
                                        isDark
                                            ? 'text-xs text-slate-400'
                                            : 'text-xs text-slate-500'
                                    }
                                >
                                    Recent reports, sectoral studies and guides
                                    available for download.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3 text-xs">
                                <ul className="space-y-2">
                                    <li className="flex items-center gap-3">
                                        <div
                                            className={[
                                                'h-11 w-8 rounded bg-gradient-to-b',
                                                isDark
                                                    ? 'from-sky-500/80 to-slate-900'
                                                    : 'from-sky-400 to-sky-700',
                                            ].join(' ')}
                                        />
                                        <div>
                                            <p className="text-[13px] font-medium">
                                                Indian Exports – Outlook 2025
                                            </p>
                                            <p
                                                className={
                                                    isDark
                                                        ? 'text-[11px] text-slate-400'
                                                        : 'text-[11px] text-slate-500'
                                                }
                                            >
                                                Annual flagship publication
                                            </p>
                                        </div>
                                    </li>
                                    <li className="flex items-center gap-3">
                                        <div
                                            className={[
                                                'h-11 w-8 rounded bg-gradient-to-b',
                                                isDark
                                                    ? 'from-emerald-500/80 to-slate-900'
                                                    : 'from-emerald-400 to-emerald-700',
                                            ].join(' ')}
                                        />
                                        <div>
                                            <p className="text-[13px] font-medium">
                                                Handbook for First-time
                                                Exporters
                                            </p>
                                            <p
                                                className={
                                                    isDark
                                                        ? 'text-[11px] text-slate-400'
                                                        : 'text-[11px] text-slate-500'
                                                }
                                            >
                                                MSME-focused guide
                                            </p>
                                        </div>
                                    </li>
                                    <li className="flex items-center gap-3">
                                        <div
                                            className={[
                                                'h-11 w-8 rounded bg-gradient-to-b',
                                                isDark
                                                    ? 'from-violet-500/80 to-slate-900'
                                                    : 'from-violet-400 to-violet-700',
                                            ].join(' ')}
                                        />
                                        <div>
                                            <p className="text-[13px] font-medium">
                                                Market Intelligence – Emerging
                                                Geographies
                                            </p>
                                            <p
                                                className={
                                                    isDark
                                                        ? 'text-[11px] text-slate-400'
                                                        : 'text-[11px] text-slate-500'
                                                }
                                            >
                                                Sector and region-wise briefs
                                            </p>
                                        </div>
                                    </li>
                                </ul>
                                <a
                                    href="#"
                                    className={
                                        isDark
                                            ? 'text-sky-300 hover:text-sky-200'
                                            : 'text-sky-600 hover:text-sky-500'
                                    }
                                >
                                    Browse all publications →
                                </a>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Row 3: Image carousel preview */}
                    <div
                        className={[
                            'rounded-3xl border px-4 py-4 backdrop-blur-xl transition-colors duration-300',
                            isDark
                                ? 'border-white/10 bg-slate-950/60 text-slate-100'
                                : 'border-slate-200 bg-white text-slate-800 shadow-sm',
                        ].join(' ')}
                    >
                        <div className="flex items-center justify-between gap-2">
                            <div>
                                <p
                                    className={
                                        isDark
                                            ? 'text-[11px] font-medium tracking-[0.2em] text-slate-400 uppercase'
                                            : 'text-[11px] font-medium tracking-[0.2em] text-slate-500 uppercase'
                                    }
                                >
                                    Visual stories
                                </p>
                                <p
                                    className={
                                        isDark
                                            ? 'text-sm font-semibold text-slate-50'
                                            : 'text-sm font-semibold text-slate-900'
                                    }
                                >
                                    Image carousel – events, delegations and
                                    initiatives
                                </p>
                            </div>
                            <p
                                className={
                                    isDark
                                        ? 'hidden text-[11px] text-slate-400 sm:block'
                                        : 'hidden text-[11px] text-slate-500 sm:block'
                                }
                            >
                                Final carousel will pull curated images with
                                captions from the media gallery.
                            </p>
                        </div>

                        <div className="mt-4 overflow-x-auto">
                            <div className="flex gap-4 pb-2">
                                {/* Simple scrollable "slides" for preview */}
                                {[1, 2, 3, 4].map((idx) => (
                                    <div
                                        key={idx}
                                        className={[
                                            'relative h-40 w-64 shrink-0 overflow-hidden rounded-2xl border',
                                            isDark
                                                ? 'border-slate-700 bg-gradient-to-br from-slate-800 via-slate-900 to-slate-950'
                                                : 'border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-100',
                                        ].join(' ')}
                                    >
                                        <div className="absolute inset-0 opacity-40">
                                            {/* Placeholder gradient; later replaced by real images */}
                                        </div>
                                        <div className="relative flex h-full flex-col justify-between p-3 text-[11px]">
                                            <span
                                                className={[
                                                    'self-start rounded-full px-2 py-1',
                                                    idx % 2 === 0
                                                        ? isDark
                                                            ? 'border border-emerald-500/40 bg-emerald-500/20 text-emerald-100'
                                                            : 'border border-emerald-300 bg-emerald-50 text-emerald-700'
                                                        : isDark
                                                          ? 'border border-sky-500/40 bg-sky-500/20 text-sky-100'
                                                          : 'border border-sky-300 bg-sky-50 text-sky-700',
                                                ].join(' ')}
                                            >
                                                {idx % 2 === 0
                                                    ? 'Overseas delegation'
                                                    : 'Domestic event'}
                                            </span>
                                            <div>
                                                <p className="text-xs font-medium">
                                                    Sample image {idx}
                                                </p>
                                                <p
                                                    className={
                                                        isDark
                                                            ? 'text-[11px] text-slate-300'
                                                            : 'text-[11px] text-slate-600'
                                                    }
                                                >
                                                    This slide will show event
                                                    photos, pavilion views and
                                                    signature initiatives.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                {/* Roadmap */}
                <section
                    id="roadmap"
                    className="mx-auto mt-10 w-full max-w-6xl text-xs"
                >
                    <div
                        className={[
                            'flex flex-col gap-4 rounded-3xl border border-dashed p-4 backdrop-blur-xl transition-colors duration-300 lg:flex-row lg:items-center lg:justify-between lg:p-6',
                            isDark
                                ? 'border-slate-700/70 bg-slate-950/40 text-slate-300'
                                : 'border-slate-300/80 bg-white text-slate-700 shadow-sm',
                        ].join(' ')}
                    >
                        <div>
                            <p
                                className={[
                                    'text-[11px] font-medium tracking-[0.2em] uppercase',
                                    isDark
                                        ? 'text-slate-400'
                                        : 'text-slate-500',
                                ].join(' ')}
                            >
                                High-level roadmap
                            </p>
                            <h3
                                className={[
                                    'mt-1 text-lg font-semibold',
                                    isDark ? 'text-slate-50' : 'text-slate-900',
                                ].join(' ')}
                            >
                                From prototype to production-ready ecosystem
                            </h3>
                            <p className="mt-2 max-w-xl">
                                This page is a design prototype only. Each
                                section will be backed by Laravel APIs,
                                Filament-based back office panels and secure
                                integrations with DGFT and other partners.
                            </p>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-3">
                            <div
                                className={[
                                    'rounded-2xl border px-4 py-3',
                                    isDark
                                        ? 'border-sky-500/40 bg-sky-500/5'
                                        : 'border-sky-300/60 bg-sky-50',
                                ].join(' ')}
                            >
                                <p
                                    className={[
                                        'text-[11px] tracking-[0.18em] uppercase',
                                        isDark
                                            ? 'text-sky-300'
                                            : 'text-sky-600',
                                    ].join(' ')}
                                >
                                    Phase 1
                                </p>
                                <p className="mt-1 font-medium">Core portals</p>
                                <p className="mt-1 text-[11px]">
                                    Members, non-members and employee panels.
                                </p>
                            </div>
                            <div
                                className={[
                                    'rounded-2xl border px-4 py-3',
                                    isDark
                                        ? 'border-emerald-500/40 bg-emerald-500/5'
                                        : 'border-emerald-300/60 bg-emerald-50',
                                ].join(' ')}
                            >
                                <p
                                    className={[
                                        'text-[11px] tracking-[0.18em] uppercase',
                                        isDark
                                            ? 'text-emerald-300'
                                            : 'text-emerald-700',
                                    ].join(' ')}
                                >
                                    Phase 2
                                </p>
                                <p className="mt-1 font-medium">
                                    Deep integrations
                                </p>
                                <p className="mt-1 text-[11px]">
                                    DGFT, NDML, airport partners and analytics.
                                </p>
                            </div>
                            <div
                                className={[
                                    'rounded-2xl border px-4 py-3',
                                    isDark
                                        ? 'border-violet-500/40 bg-violet-500/5'
                                        : 'border-violet-300/60 bg-violet-50',
                                ].join(' ')}
                            >
                                <p
                                    className={[
                                        'text-[11px] tracking-[0.18em] uppercase',
                                        isDark
                                            ? 'text-violet-300'
                                            : 'text-violet-700',
                                    ].join(' ')}
                                >
                                    Phase 3
                                </p>
                                <p className="mt-1 font-medium">
                                    Smart recommendations
                                </p>
                                <p className="mt-1 text-[11px]">
                                    AI-assisted policy feedback and opportunity
                                    discovery.
                                </p>
                            </div>
                        </div>
                    </div>

                    <p
                        className={[
                            'mt-4 text-[11px]',
                            isDark ? 'text-slate-500' : 'text-slate-500',
                        ].join(' ')}
                    >
                        Internal note: This is a design-only preview built on
                        the existing React + ShadCN boilerplate. All navigation
                        items, metrics and content are placeholders and can be
                        aligned with final requirements.
                    </p>
                </section>
            </div>
        </>
    );
}
