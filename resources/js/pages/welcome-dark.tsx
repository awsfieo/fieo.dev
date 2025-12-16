import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardHeader,
    CardContent,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Separator } from '@/components/ui/separator';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="FIEO Digital Gateway">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                    rel="stylesheet"
                />
            </Head>

            <div className="min-h-screen bg-gradient-to-b from-[#050816] via-[#050816] to-[#020617] px-4 pb-10 pt-4 text-slate-50 lg:px-10 lg:pt-6">
                {/* Top navigation */}
                <header className="mx-auto flex w-full max-w-6xl items-center justify-between rounded-full border border-white/5 bg-black/30 px-4 py-2 backdrop-blur-lg">
                    <div className="flex items-center gap-2">
                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-400 via-emerald-400 to-sky-500 shadow-lg">
                            <span className="text-xs font-semibold tracking-[0.16em]">
                                F
                            </span>
                        </div>
                        <div className="flex flex-col leading-tight">
                            <span className="text-xs uppercase tracking-[0.2em] text-slate-400">
                                FIEO
                            </span>
                            <span className="text-sm font-medium text-slate-50">
                                Digital Gateway
                            </span>
                        </div>
                    </div>

                    <nav className="hidden items-center gap-6 text-xs font-medium text-slate-300 md:flex">
                        <a href="#overview" className="hover:text-white">
                            Overview
                        </a>
                        <a href="#ecosystem" className="hover:text-white">
                            Ecosystem
                        </a>
                        <a href="#experiences" className="hover:text-white">
                            Experiences
                        </a>
                        <a href="#roadmap" className="hover:text-white">
                            Roadmap
                        </a>
                    </nav>

                    <div className="flex items-center gap-2 text-xs">
                        {auth.user ? (
                            <Link href={dashboard()}>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    className="border-slate-500/60 bg-white/5 text-slate-100 hover:border-sky-400 hover:bg-sky-500/10 hover:text-white"
                                >
                                    Go to dashboard
                                </Button>
                            </Link>
                        ) : (
                            <>
                                <a
                                    href="/admin"
                                    className="hidden rounded-full px-3 py-1 text-xs text-slate-300 hover:bg-white/5 md:inline-block"
                                >
                                    Admin panel
                                </a>
                                <a
                                    href="/employee"
                                    className="hidden rounded-full px-3 py-1 text-xs text-slate-300 hover:bg-white/5 md:inline-block"
                                >
                                    Employee panel
                                </a>
                                <Link href={login()}>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        className="text-slate-200 hover:bg-white/5"
                                    >
                                        Log in
                                    </Button>
                                </Link>
                                <Link href={register()}>
                                    <Button
                                        size="sm"
                                        className="bg-sky-500 text-slate-950 shadow-lg shadow-sky-500/30 hover:bg-sky-400"
                                    >
                                        Join FIEO
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
                        className="flex flex-1 flex-col gap-6 rounded-3xl border border-white/10 bg-gradient-to-br from-slate-900/80 via-slate-900/60 to-slate-900/40 p-6 shadow-[0_0_80px_rgba(56,189,248,0.35)] backdrop-blur-2xl lg:p-8"
                    >
                        <div className="inline-flex items-center gap-2 rounded-full border border-emerald-400/40 bg-emerald-500/10 px-3 py-1 text-[10px] font-medium uppercase tracking-[0.2em] text-emerald-200">
                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_0_4px_rgba(52,211,153,0.4)]" />
                            Live preview · New FIEO digital ecosystem
                        </div>

                        <div className="space-y-4">
                            <h1 className="text-balance text-3xl font-semibold tracking-tight text-slate-50 sm:text-4xl lg:text-5xl">
                                One unified platform for India&apos;s exporters,
                                policymakers and FIEO teams.
                            </h1>
                            <p className="max-w-xl text-sm leading-relaxed text-slate-300 sm:text-[15px]">
                                This preview home page demonstrates how the new
                                FIEO digital experience can welcome members,
                                non-members, employees and partners into a
                                single, secure, API-first ecosystem powered by
                                modern React and ShadCN UI.
                            </p>

                            <div className="flex flex-wrap gap-2 text-[11px]">
                                <Badge
                                    variant="outline"
                                    className="border-sky-400/50 bg-sky-500/10 text-sky-100"
                                >
                                    Members & e-RCMC
                                </Badge>
                                <Badge
                                    variant="outline"
                                    className="border-emerald-400/50 bg-emerald-500/10 text-emerald-100"
                                >
                                    Events & delegations
                                </Badge>
                                <Badge
                                    variant="outline"
                                    className="border-violet-400/50 bg-violet-500/10 text-violet-100"
                                >
                                    Policy & trade facilitation
                                </Badge>
                                <Badge
                                    variant="outline"
                                    className="border-amber-300/60 bg-amber-500/10 text-amber-100"
                                >
                                    Employee workspace
                                </Badge>
                            </div>
                        </div>

                        <div className="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div className="flex flex-wrap gap-2">
                                <Link href={login()}>
                                    <Button className="bg-sky-500 text-slate-950 shadow-lg shadow-sky-500/30 hover:bg-sky-400">
                                        Enter members area
                                    </Button>
                                </Link>
                                <a href="/admin">
                                    <Button
                                        variant="outline"
                                        className="border-slate-500/70 bg-white/5 text-slate-100 hover:border-sky-400 hover:bg-sky-500/10"
                                    >
                                        Preview admin console
                                    </Button>
                                </a>
                            </div>
                            <p className="text-[11px] text-slate-400">
                                All panels share a single identity, data and
                                governance layer.
                            </p>
                        </div>

                        {/* Stats row */}
                        <Separator className="my-4 border-slate-700/60" />
                        <div className="grid grid-cols-2 gap-4 text-xs sm:grid-cols-4">
                            <div>
                                <p className="text-[11px] uppercase tracking-[0.18em] text-slate-400">
                                    Members onboarded
                                </p>
                                <p className="mt-1 text-lg font-semibold text-sky-300">
                                    40k+
                                </p>
                            </div>
                            <div>
                                <p className="text-[11px] uppercase tracking-[0.18em] text-slate-400">
                                    Annual events
                                </p>
                                <p className="mt-1 text-lg font-semibold text-emerald-300">
                                    350+
                                </p>
                            </div>
                            <div>
                                <p className="text-[11px] uppercase tracking-[0.18em] text-slate-400">
                                    Trade policy updates
                                </p>
                                <p className="mt-1 text-lg font-semibold text-violet-300">
                                    Real-time
                                </p>
                            </div>
                            <div>
                                <p className="text-[11px] uppercase tracking-[0.18em] text-slate-400">
                                    Single sign-on
                                </p>
                                <p className="mt-1 text-lg font-semibold text-amber-200">
                                    Multi-panel
                                </p>
                            </div>
                        </div>
                    </section>

                    {/* Logo / hero visual */}
                    <section className="flex flex-1 items-stretch">
                        <Card className="flex w-full flex-col justify-between rounded-3xl border-white/10 bg-gradient-to-br from-slate-900/70 via-slate-900/40 to-slate-900/20/60 p-0 text-slate-50 shadow-[0_0_60px_rgba(129,140,248,0.5)] backdrop-blur-2xl">
                            <CardHeader className="space-y-1 px-6 pt-6">
                                <CardTitle className="text-sm font-medium text-slate-200">
                                    FIEO brand in a digital canvas
                                </CardTitle>
                                <CardDescription className="text-xs text-slate-400">
                                    Centered logo with adaptive layout. This
                                    panel can evolve into a live data
                                    visualisation, heatmap of exports or
                                    personalised announcements.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-1 items-center justify-center px-4 pb-6 pt-2">
                                <div className="relative flex aspect-square w-full max-w-sm items-center justify-center rounded-2xl bg-radial from-sky-500/40 via-slate-900 to-slate-950">
                                    <div className="pointer-events-none absolute inset-10 rounded-[32px] border border-white/10 bg-white/2 shadow-[0_0_120px_rgba(56,189,248,0.45)]" />
                                    <div className="relative flex items-center justify-center">
                                        {/* Existing FIEO SVG */}
                                        <svg
                                            version="1.1"
                                            viewBox="0 0 1512 1536"
                                            className="h-56 w-56 drop-shadow-[0_30px_80px_rgba(15,23,42,0.9)]"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            {/* SVG paths as in your current file */}
                                            {/* ...keeping entire SVG exactly the same... */}
                                        </svg>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </section>
                </main>

                {/* Ecosystem section */}
                <section
                    id="ecosystem"
                    className="mx-auto mt-10 grid w-full max-w-6xl gap-6 lg:grid-cols-3"
                >
                    <Card className="border-white/10 bg-slate-900/60 text-slate-100 backdrop-blur-xl">
                        <CardHeader>
                            <CardTitle className="text-sm">
                                Exporter experience
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-400">
                                A unified workspace for membership, events,
                                certifications and policy updates.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="text-xs text-slate-300">
                            <ul className="space-y-2">
                                <li>Single sign-on across all FIEO services</li>
                                <li>Smart onboarding with DGFT and RCMC data</li>
                                <li>
                                    Guided journeys for new exporters, MSMEs and
                                    women-led businesses
                                </li>
                            </ul>
                        </CardContent>
                    </Card>

                    <Card className="border-white/10 bg-slate-900/60 text-slate-100 backdrop-blur-xl">
                        <CardHeader>
                            <CardTitle className="text-sm">
                                Internal operations
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-400">
                                Separate employee and admin panels built on the
                                same React and Filament core.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="text-xs text-slate-300">
                            <ul className="space-y-2">
                                <li>Tour claims, TA/DA and HR workflows</li>
                                <li>Event management with TDS and partial pay</li>
                                <li>Role-based access and audit history</li>
                            </ul>
                        </CardContent>
                    </Card>

                    <Card className="border-white/10 bg-slate-900/60 text-slate-100 backdrop-blur-xl">
                        <CardHeader>
                            <CardTitle className="text-sm">
                                Policy and analytics
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-400">
                                Real-time insights to support DGFT, ministries
                                and sector councils.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="text-xs text-slate-300">
                            <ul className="space-y-2">
                                <li>Dashboard of exports, sectors and regions</li>
                                <li>
                                    Deep links to trade facilitation initiatives
                                </li>
                                <li>
                                    Structured feedback loops from exporters to
                                    policy teams
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </section>

                {/* Experiences section */}
                <section
                    id="experiences"
                    className="mx-auto mt-10 w-full max-w-6xl"
                >
                    <Tabs
                        defaultValue="exporters"
                        className="rounded-3xl border border-white/10 bg-slate-950/40 p-4 backdrop-blur-xl lg:p-6"
                    >
                        <div className="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                            <div>
                                <p className="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">
                                    Preview journeys
                                </p>
                                <h2 className="mt-1 text-lg font-semibold text-slate-50">
                                    How different stakeholders will experience
                                    the new FIEO portal
                                </h2>
                            </div>
                            <TabsList className="bg-slate-900/60">
                                <TabsTrigger value="exporters" className="text-xs">
                                    Exporters
                                </TabsTrigger>
                                <TabsTrigger value="employees" className="text-xs">
                                    Employees
                                </TabsTrigger>
                                <TabsTrigger value="partners" className="text-xs">
                                    Partners
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <Separator className="my-4 border-slate-800" />

                        <TabsContent
                            value="exporters"
                            className="text-xs text-slate-300 lg:text-[13px]"
                        >
                            <ol className="grid gap-4 sm:grid-cols-3">
                                <li className="rounded-2xl border border-sky-500/40 bg-sky-500/5 p-4">
                                    <p className="text-[11px] uppercase tracking-[0.18em] text-sky-300">
                                        Step 1
                                    </p>
                                    <p className="mt-2 font-medium text-slate-50">
                                        Guided onboarding
                                    </p>
                                    <p className="mt-1 text-slate-300">
                                        Import IEC and DGFT data, verify RCMC
                                        and create a unified exporter profile.
                                    </p>
                                </li>
                                <li className="rounded-2xl border border-sky-500/20 bg-sky-500/5 p-4">
                                    <p className="text-[11px] uppercase tracking-[0.18em] text-sky-200">
                                        Step 2
                                    </p>
                                    <p className="mt-2 font-medium text-slate-50">
                                        Discover opportunities
                                    </p>
                                    <p className="mt-1 text-slate-300">
                                        Personalised list of events, delegations
                                        and schemes based on sector and region.
                                    </p>
                                </li>
                                <li className="rounded-2xl border border-sky-500/20 bg-sky-500/5 p-4">
                                    <p className="text-[11px] uppercase tracking-[0.18em] text-sky-200">
                                        Step 3
                                    </p>
                                    <p className="mt-2 font-medium text-slate-50">
                                        Track everything in one place
                                    </p>
                                    <p className="mt-1 text-slate-300">
                                        Registrations, payments, approvals and
                                        certificates, all in a single timeline.
                                    </p>
                                </li>
                            </ol>
                        </TabsContent>

                        <TabsContent
                            value="employees"
                            className="text-xs text-slate-300 lg:text-[13px]"
                        >
                            <div className="grid gap-4 sm:grid-cols-3">
                                <div className="rounded-2xl border border-violet-500/40 bg-violet-500/5 p-4">
                                    Streamlined event creation and approvals.
                                </div>
                                <div className="rounded-2xl border border-violet-500/30 bg-violet-500/5 p-4">
                                    Integrated TA/DA, tour claims and travel
                                    policies.
                                </div>
                                <div className="rounded-2xl border border-violet-500/30 bg-violet-500/5 p-4">
                                    Role-aware dashboards for regions, MRD and
                                    policy divisions.
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent
                            value="partners"
                            className="text-xs text-slate-300 lg:text-[13px]"
                        >
                            <div className="grid gap-4 sm:grid-cols-3">
                                <div className="rounded-2xl border border-emerald-500/40 bg-emerald-500/5 p-4">
                                    Embed payment gateways, airport services and
                                    other APIs behind a consistent FIEO layer.
                                </div>
                                <div className="rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-4">
                                    Dedicated partner views for NDML, Adani and
                                    other service providers.
                                </div>
                                <div className="rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-4">
                                    Data sharing built on explicit consent and
                                    strong information security.
                                </div>
                            </div>
                        </TabsContent>
                    </Tabs>
                </section>

                {/* Roadmap */}
                <section
                    id="roadmap"
                    className="mx-auto mt-10 w-full max-w-6xl text-xs text-slate-300"
                >
                    <div className="flex flex-col gap-4 rounded-3xl border border-dashed border-slate-700/70 bg-slate-950/40 p-4 backdrop-blur-xl lg:flex-row lg:items-center lg:justify-between lg:p-6">
                        <div>
                            <p className="text-[11px] font-medium uppercase tracking-[0.2em] text-slate-400">
                                High-level roadmap
                            </p>
                            <h3 className="mt-1 text-lg font-semibold text-slate-50">
                                From prototype to production-ready ecosystem
                            </h3>
                            <p className="mt-2 max-w-xl text-slate-300">
                                This page is a design prototype only. Each
                                section will be backed by Laravel APIs,
                                Filament-based back office panels and secure
                                integrations with DGFT and other partners.
                            </p>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-3">
                            <div className="rounded-2xl border border-sky-500/40 bg-sky-500/5 px-4 py-3">
                                <p className="text-[11px] uppercase tracking-[0.18em] text-sky-300">
                                    Phase 1
                                </p>
                                <p className="mt-1 font-medium text-slate-50">
                                    Core portals
                                </p>
                                <p className="mt-1 text-[11px] text-slate-300">
                                    Members, non-members and employee panels.
                                </p>
                            </div>
                            <div className="rounded-2xl border border-emerald-500/40 bg-emerald-500/5 px-4 py-3">
                                <p className="text-[11px] uppercase tracking-[0.18em] text-emerald-300">
                                    Phase 2
                                </p>
                                <p className="mt-1 font-medium text-slate-50">
                                    Deep integrations
                                </p>
                                <p className="mt-1 text-[11px] text-slate-300">
                                    DGFT, NDML, airport partners and analytics.
                                </p>
                            </div>
                            <div className="rounded-2xl border border-violet-500/40 bg-violet-500/5 px-4 py-3">
                                <p className="text-[11px] uppercase tracking-[0.18em] text-violet-300">
                                    Phase 3
                                </p>
                                <p className="mt-1 font-medium text-slate-50">
                                    Smart recommendations
                                </p>
                                <p className="mt-1 text-[11px] text-slate-300">
                                    AI-assisted policy feedback and opportunity
                                    discovery.
                                </p>
                            </div>
                        </div>
                    </div>

                    <p className="mt-4 text-[11px] text-slate-500">
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
