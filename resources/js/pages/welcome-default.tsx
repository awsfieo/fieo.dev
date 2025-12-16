import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
                <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
                    <nav className="flex items-center justify-end gap-4">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <a
                                    href="/admin"
                                    className="px-4 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Admin Panel
                                </a>

                                <a
                                    href="/employee"
                                    className="px-4 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Employee Panel
                                </a>

                                <Link
                                    href={login()}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={register()}
                                    className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </header>
                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <main className="flex w-full max-w-[335px] flex-col-reverse rounded-lg border border-[#e3e3e0] bg-white shadow-sm lg:max-w-4xl lg:flex-row dark:border-[#3E3E3A] dark:bg-[#161615]">
                        <div className="flex-1 border-b border-[#f0f0ec] p-6 pb-12 text-[13px] leading-[20px] lg:border-r lg:border-b-0 dark:border-[#3E3E3A] dark:text-[#EDEDEC]">
                            <h1 className="mb-1 font-medium">
                                FIEO's New Development Server
                            </h1>
                            <p className="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                                <br />
                                We are building an incredibly rich ecosystem.
                                <br />
                                <br />
                                We advise you to start with the following.
                            </p>
                            <ul className="mb-4 flex flex-col lg:mb-6">
                                <li className="relative flex items-center gap-4 py-2 before:absolute before:top-1/2 before:bottom-0 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                        </span>
                                    </span>
                                    <span>
                                        Join us -
                                        <a
                                            href="/register"
                                            target="_blank"
                                            className="ml-1 inline-flex items-center space-x-1 font-medium text-[#f53003] underline underline-offset-4 dark:text-[#FF4433]"
                                        >
                                            <span>Register</span>
                                            <svg
                                                width={10}
                                                height={11}
                                                viewBox="0 0 10 11"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-2.5 w-2.5"
                                            >
                                                <path
                                                    d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                                                    stroke="currentColor"
                                                    strokeLinecap="square"
                                                />
                                            </svg>
                                        </a>
                                    </span>
                                </li>
                                <li className="relative flex items-center gap-4 py-2 before:absolute before:top-0 before:bottom-1/2 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                        </span>
                                    </span>
                                    <span>
                                        Give your
                                        <a
                                            href="/feedback"
                                            target="_blank"
                                            className="ml-1 inline-flex items-center space-x-1 font-medium text-[#f53003] underline underline-offset-4 dark:text-[#FF4433]"
                                        >
                                            <span>Feedback</span>
                                            <svg
                                                width={10}
                                                height={11}
                                                viewBox="0 0 10 11"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-2.5 w-2.5"
                                            >
                                                <path
                                                    d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                                                    stroke="currentColor"
                                                    strokeLinecap="square"
                                                />
                                            </svg>
                                        </a>
                                    </span>
                                </li>
                            </ul>
                            <ul className="flex gap-3 text-sm leading-normal">
                                <li>
                                    <a
                                        href="/login"
                                        target="_blank"
                                        className="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                                    >
                                        Login
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div className="relative -mb-px flex aspect-[335/376] w-full shrink-0 items-center justify-center overflow-hidden bg-white lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-[438px] dark:bg-white">
                            <svg
                                version="1.1"
                                viewBox="0 0 1512 1536"
                                width="378"
                                height="384"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    transform="translate(755,34)"
                                    d="m0 0 4 1 18 18 8 7 29 29 8 7 52 52 2 1v2l4 2 620 620-1 4-48 48h-2l-2 4h-2v2l-7 6-231 231h-2v2l-8 7-68 68h-2l-1 3-6 5-6 7-8 7-197 197h-2l-2 4h-2l-2 4h-2v2l-7 6-127 127-7-6-9-9-8-7-41-41-8-7-24-24-8-7-24-24-8-7-70-70-8-7-34-34-8-7-25-25-8-7-25-25-8-7-68-68-8-7-29-29-8-7-18-18-8-7-21-21-8-7-57-57-3-2v-2l-4-2-33-33-8-7-18-18-8-7-21-21-8-7-57-57-8-7-29-29-2-1v-2l-4-2v-3l431-431 5-6 6-5 7-8 136-136h2l1-3 6-5 6-7 8-7 69-69h2l2-4 8-7 33-33 8-7 9-9z"
                                    fill="#FCFCFC"
                                />
                                <path
                                    transform="translate(152,634)"
                                    d="m0 0v3l-132 132v4l8 7 89 89 6 5 5 6 7 6 5 6 7 6 5 6 7 6 5 6 7 6 5 5 5 4 3-3 25-1h353l5 2 33 33 7 8 10 10 8 7v2l4 2 126 126 3 2 1 11v136l1 233-2 2-9-9-8-7-41-41-8-7-24-24-8-7-24-24-8-7-70-70-8-7-34-34-8-7-25-25-8-7-25-25-8-7-68-68-8-7-29-29-8-7-18-18-8-7-21-21-8-7-57-57-3-2v-2l-4-2-33-33-8-7-18-18-8-7-21-21-8-7-57-57-8-7-29-29-2-1v-2l-4-2v-3z"
                                    fill="#F7CD38"
                                />
                                <path
                                    transform="translate(757,35)"
                                    d="m0 0 4 2 16 16 8 7 29 29 8 7 52 52 2 1v2l4 2 71 71-2 5-43 43h-2l-1 3-8 7-66 66h-2v2l-8 7-60 60 2 5 8 7v2h2v2h2v2l4 2 94 94 7 8 46 46 3 1 2 5h2l18 18 1 4-393 1-97-1-221-1-52-1-2-2 1-4 7-8 168-168 10-7 4 1 86 86 7 8 59 59 7 8 11 11 8 7 8 8v2h2v-367l1-13h4l10 9 11 11 2 1v2h2v2h2v2h2v2h2v2l4 2 41 41 4 3v2l4 2v2l4 2 32 32v2l4 2 49 49 2 1v2h2v2h2l5 5 2 4h3l-1-379z"
                                    fill="#7762A8"
                                />
                                <path
                                    transform="translate(951,225)"
                                    d="m0 0 7 6 380 380-3 3h-494v-1h109l-4-6-11-11-4-3v-2h-2l-2-5-4-1-52-52-7-8-89-89-2-1v-2h-2v-2h-2l-5-5-6-7 2-4 65-65h2v-2l8-7 62-62h2l2-4 8-7 40-40z"
                                    fill="#7BCDE3"
                                />
                                <path
                                    transform="translate(957,927)"
                                    d="m0 0h82l271 1 21 1 4 2-11 12-129 129-8 7-36 36-6 5v3l-17 16-36 36-7 8-149 149-6 7-8 7-4 5-2-1 9-10 26-26 2-4-8-5-10-9-94-94-8-7-45-45-7-8-8-7-7-8-7-6v-2l8-7 9-10 77-77h2v-2h2v-2l8-7 82-82h2v-2l3-2z"
                                    fill="#7861A7"
                                />
                                <path
                                    transform="translate(755,34)"
                                    d="m0 0 3 1-1 4v377h-3l-5-5-2-4h-2v-2h-2v-2l-4-2-85-85-6-5-47-47-2-1v-2h-2v-2h-2v-2h-2v-2h-2v-2l-4-2-16-16-4-3h-2l-1 15v361l-1 4h-2l-7-8-7-7-8-7-12-12-7-8-59-59-7-8-80-80-3-5 14-15 163-163 4-5 8-7 17-16 14-14 1-2h2l2-4 96-96 3-5 8-7 33-33 8-7 9-9z"
                                    fill="#7DC79D"
                                />
                                <path
                                    transform="translate(756,419)"
                                    d="m0 0h3l6 7 4 3v2h2v2h2v2l4 2 94 94 7 8 46 46 3 1 2 5h2l18 18 1 4-393 1-97-1-221-1-52-1-2-2 1-4 7-8 168-168 10-7 4 1 86 86 7 8 59 59 7 8 11 11 8 7 8 8v2h2l1-3 11-9 10-10 8-7 4-5 5-5h2l1-3 6-5 6-7 6-5 6-7h2l2-4h2l2-4 6-5 6-7 6-5 76-76 6-7 8-7 10-10z"
                                    fill="#E65025"
                                />
                                <path
                                    transform="translate(1080,638)"
                                    d="m0 0h18l24 2 24 5 17 6 17 8 12 8 13 10 10 10 12 17 6 13 5 14 4 19 1 8v26l-5 25-6 17-8 15-8 11-12 13-10 8-14 9-19 9-19 6-14 3-25 3h-24l-32-4-19-5-17-6-15-8-14-10-10-9-7-7-10-14-8-16-5-15-4-20-1-8v-17l4-27 6-20 8-16 8-11 9-10 8-8 14-10 16-9 16-6 25-6 14-2z"
                                    fill="#04AEF4"
                                />
                                <path
                                    transform="translate(152,634)"
                                    d="m0 0v3l-132 132v4l8 7 89 89 6 5 5 6 7 6 5 6 7 6 5 6 7 6 5 6 7 6 5 5 5 4 3-3 25-1h353l4 2-7 3-4 5-8 7-124 124h-2v2l-8 7-41 41 1 4 2 1-1 2-8-7-29-29-8-7-18-18-8-7-21-21-8-7-57-57-3-2v-2l-4-2-33-33-8-7-18-18-8-7-21-21-8-7-57-57-8-7-29-29-2-1v-2l-4-2v-3z"
                                    fill="#AD5CA1"
                                />
                                <path
                                    transform="translate(661,643)"
                                    d="m0 0h230l1 1 1 44-3 1h-28l-138-1v20l1 34h154l2 2v42l-1 1h-154l-2-1v67l22-1h151l2 1v44l-14 1h-90l-135-1v-253z"
                                    fill="#03ADF4"
                                />
                                <path
                                    transform="translate(1144,418)"
                                    d="m0 0 7 6 187 187-3 3h-494v-1h109l1-3 10-7 27-27 5-6h2l2-4 129-129 6-7 8-7z"
                                    fill="#F3D279"
                                />
                                <path
                                    transform="translate(563,929)"
                                    d="m0 0h3v206l1 95v75l-2 6-7-6-8-7-34-34-8-7-25-25-8-7-25-25-8-7-67-67v-3l-4-2 1-4 47-47h2v-2l8-7 129-129z"
                                    fill="#EA7928"
                                />
                                <path
                                    transform="translate(759,1121)"
                                    d="m0 0 4 2 10 10v2l4 2 11 11 7 8 44 44 8 7 95 95 7 5 5 2-3 6-32 32-5 8-51 51-6 5-6 7-6 5-6 7-6 5-6 7-6 5-35 35h-2l-2 4h-2l-2 4-9 9-2 3h-3l-1 3-4-3-1-236v-143z"
                                    fill="#0197CF"
                                />
                                <path
                                    transform="translate(757,35)"
                                    d="m0 0 4 2 16 16 8 7 29 29 8 7 52 52 2 1v2l4 2 71 71-2 5-43 43h-2l-1 3-8 7-66 66h-2v2l-8 7-29 29-5 3-20 20h-2l-1 3-4 2-1-25-1-352z"
                                    fill="#AE5CA1"
                                />
                                <path
                                    transform="translate(566,222)"
                                    d="m0 0 2 1-4 5 1 4-1 15v361l-1 4h-2l-7-8-7-7-8-7-12-12-7-8-59-59-7-8-80-80-3-5 14-15 163-163 4-5 8-7z"
                                    fill="#6FCAC1"
                                />
                                <path
                                    transform="translate(830,927)"
                                    d="m0 0h122l-1 3h-2v2l-8 7-82 82h-2v2h-2v2l-8 7-77 77-7 8-4 2-1 4-1-4-8-7-122-122-8-7v-2l-4-2-11-11-7-8-31-31v-1z"
                                    fill="#6ECAC2"
                                />
                                <path
                                    transform="translate(957,927)"
                                    d="m0 0h82l271 1 21 1 4 2-11 12-129 129-8 7-36 36-6 5h-2l-4-5v-2l-4-2-156-156v-2l-4-2-15-15v-2l-5-2-2-4z"
                                    fill="#E5452B"
                                />
                                <path
                                    transform="translate(954,931)"
                                    d="m0 0 6 3 7 8 175 175 4 5-7 7-8 7-39 39-7 8-149 149-6 7-8 7-4 5-2-1 9-10 26-26 2-5v-374z"
                                    fill="#7ABE4E"
                                />
                                <path
                                    transform="translate(371,422)"
                                    d="m0 0 4 1 86 86 7 8 59 59 7 8 11 11 8 7 8 8v2h8v1l-12 1-97-1-221-1-52-1-2-2 1-4 7-8 168-168z"
                                    fill="#EA7928"
                                />
                                <path
                                    transform="translate(757,1123)"
                                    d="m0 0h1l1 7v136l1 233-2 2-9-9-8-7-41-41-8-7-24-24-8-7-24-24-8-7-60-60 1-6 20-20 8-7 50-50h2l2-4 9-8 5-6 8-7 67-67 7-8 9-7z"
                                    fill="#7ABE4E"
                                />
                                <path
                                    transform="translate(953,234)"
                                    d="m0 0 5 1 4 2v2h2l7 8 47 47v2l4 2v2h2v2h2v2h2v2h2v2h2v2h2v2h2l6 7 6 5 6 7 6 5 6 7 6 5 6 7 32 32 2 1v2h2v2l4 2v2h2v2h2v2l4 2v2h2l5 6 7 6 2 4-3 5-11 10-7 8-130 130-9 10-23 23-7 4v-375z"
                                    fill="#7ABE4E"
                                />
                                <path
                                    transform="translate(299,642)"
                                    d="m0 0h103l84 1 2 2-1 43-7 1h-143v56l130 1 1 4-1 40-15 1h-115v106h-63l-1-1v-251l1-2z"
                                    fill="#04ADF4"
                                />
                                <path
                                    transform="translate(177,609)"
                                    d="m0 0 2 3 1 3v45l1 268 3 1-3 3-10-8-5-6-7-6-5-6-7-6-5-6-7-6-5-6-7-6-4-4v-2l-4-2-93-93-3-2 1-5 132-132 3-5z"
                                    fill="#02ADF4"
                                />
                                <path
                                    transform="translate(1337,612)"
                                    d="m0 0 4 2 155 155-1 5-13 12-42 42-2 3h-2l-2 4h-2l-2 4-44 44h-2l-2 4h-2l-2 4-12 12h-2v2l-8 7-15 15-5 2-1-1v-307z"
                                    fill="#02ADF4"
                                />
                                <path
                                    transform="translate(1076,685)"
                                    d="m0 0h28l16 3 15 6 12 7 10 9 7 10 6 13 3 10 2 14v26l-4 20-5 13-9 13-8 8-14 9-11 5-16 4-15 2-14-1-19-4-13-5-11-6-11-9-9-12-6-13-4-14-2-13v-19l3-20 6-16 7-11 9-10 14-9 11-5 15-4z"
                                    fill="#FDFDFD"
                                />
                                <path
                                    transform="translate(539,643)"
                                    d="m0 0h60l1 1 1 253-17 1-46-1-1-1v-247z"
                                    fill="#05AEF4"
                                />
                                <path
                                    transform="translate(152,634)"
                                    d="m0 0v3l-132 132v4l8 7 89 89 6 5 5 6 7 6 5 6 7 6 5 6 7 6 5 6 7 6 5 5 5 4 3-3 25-1h348v1l-47 1-25 1v1l-14 1h-2l-6 3v2h-2v7h-24-3-10l-16-1-3-2-6-1v-1h8v-2l-2 1h-19l-8-1h-7l-4-1-4 3-2-2v-1h-22l-3 2-6 1-3-3h-8v2l-7-1-5 1-2-5-4 2-4-2-2 2h-8l-1-1h-3l-5-1-3-1v2l-7 1-5 1-3-2-5 1-1-2h-5l1 2 5 2-3 2h-2l-1-2-5 3-7 1-8 3-5 2h-5l3 9 5 6 1 3 8 7 3 1 1 3 6 4 1 5 5 2 5 5 7 8 3 2 1 5 3-1 2 4h2l1 3 3 3 5 2v2h2l2 4 4 2 6 7 5 4v3h2v2h3v2l3 1 2 5 6 4 1 5 5 2 12 12 4 5v2h3v2h2v2h2l1 3 5 4 7 10 5 5 5 4 1 4-1 6 4 4-1 2-8-7-29-29-8-7-18-18-8-7-21-21-8-7-57-57-3-2v-2l-4-2-33-33-8-7-18-18-8-7-21-21-8-7-57-57-8-7-29-29-2-1v-2l-4-2v-3z"
                                    fill="#BA65AE"
                                />
                                <path
                                    transform="translate(757,35)"
                                    d="m0 0 4 2 16 16 8 7 29 29 8 7 52 52 2 1v2l4 2 71 71-2 5-43 43h-2l-1 3-8 7-66 66-2-1 53-53 1-2h2l1-3 18-18 1-2h2v-2h2v-2h2l2-4 3-4 4-6h2l1-2 2-2 2-4h2l2-5 3-7 1-4-2-2-2-5-4-4v-2l-4-1-7-3-1-4h-2l-3-5-6-5-6-7-5-5-6-7-7-6-6-8-7-6-9-9-6-5-5-5v-2l-4-2-8-7-4-3v-2l-4-2-7-6v-2l-4-2v-2l-4-2v-2l-4-2-9-8-8-6h-2l-5-5-10-2-1-2-1 8-3 1 2 3-1 4-1 2v11l-2 5-1 6-2 9h-2l1 2v6h-2v34l-1 26h-1l-1-159z"
                                    fill="#BB67AF"
                                />
                                <path
                                    transform="translate(1496,770)"
                                    d="m0 0 4 3-1 4-48 48h-2l-2 4h-2v2l-7 6-231 231h-2v2l-8 7-68 68h-2l-1 3-6 5-6 7-8 7-197 197h-2l-2 4h-2l-2 4h-2v2l-7 6-127 127-2-1 1-4 3-1 7-8 6-7 8-7 29-29 6-5 6-7 6-5 6-7 6-5 6-7 6-5 56-56 7-8 3-3h2l1-3 5-5h2l2-4 153-153 7-8 18-18h2l2-4 18-18 8-7 5-5 10-8 5-5h2v-2l8-7 4-2 1-3 7-6 128-128 7-8 4-5 10-10 3-6 8-7 15-15h2v-2l8-7 7-7 5-6 8-7 39-39 5-6 54-54 5-4z"
                                    fill="#FDFDFD"
                                />
                                <path
                                    transform="translate(660,644)"
                                    d="m0 0h6l2 4-1 12v41l1 25v144l-1 6 1 10 13 2 15 1 60 1 9 2 1 2-7 2h-99z"
                                    fill="#04A7F3"
                                />
                                <path
                                    transform="translate(691,95)"
                                    d="m0 0 2 1-98 98-1 2h-2l-2 4-22 22-8 7-10 9-7 8-166 166-5 7 2 3-8 4-13 12-165 165-2 3 1 4 52 1 221 1v1l-15 1-201 1h-46l-18-1-2-2 1-5 266-266 5-6 6-5 7-8 136-136h2l1-3 6-5 6-7 8-7z"
                                    fill="#F5D788"
                                />
                                <path
                                    transform="translate(152,634)"
                                    d="m0 0v3l-132 132v4l8 7 89 89 6 5 5 6 7 6 5 6 7 6 5 6 7 6 5 6 7 6 5 5 7 5 7 8 139 139 6 5v2l4 2-1 2-10-9-17-17-8-7-21-21-8-7-57-57-3-2v-2l-4-2-33-33-8-7-18-18-8-7-21-21-8-7-57-57-8-7-29-29-2-1v-2l-4-2v-3z"
                                    fill="#FCFCFC"
                                />
                                <path
                                    transform="translate(1028,925)"
                                    d="m0 0h78l150 1h65l14 1 3 2-1 4-5 5-4 5h-2l-2 4-7 7-7 8-120 120h-2l-2 4-6 5-11 11h-2v2l-8 7-13 10-2-1 16-15 33-33 8-7 130-130 3-4-3-1-21-1-271-1h-87l1-2z"
                                    fill="#F4D37A"
                                />
                                <path
                                    transform="translate(1041,928)"
                                    d="m0 0h269l21 1 4 2-11 12-129 129-8 7-36 36-6 5h-2l-4-5v-2l-4-2-156-156 1-2 59 59 6 5v2h2v2h2v2h2l5 5 1 3 4 2 8 8v2l4 2 4 4v2l4 2 56 56v2l4 2 7 1 3-1 2-4 4-4h2l2-4h2l2-4h2l2-4 16-16 6-5 6-7 8-7 83-83 4-5 8-7 5-6 6-5 5-6 4-4h2l1-5 3-1-1-4-26-1-46-1h-150l-62-1z"
                                    fill="#EA7928"
                                />
                                <path
                                    transform="translate(951,225)"
                                    d="m0 0 7 6 186 186-2 5-2-4-5-6-3-2v-2l-4-2v-2h-2l-4-4v-2h-2v-2h-2l-4-4v-2h-2v-2l-4-2-8-8v-2l-4-2-16-16v-2l-4-2v-2l-4-2v-2l-4-2v-2l-4-2v-2l-4-2v-2l-4-2v-2l-4-2-4-4v-2l-4-2v-2l-4-2v-2l-4-2v-2h-2v-2h-2v-2h-2v-2h-2v-2h-2v-2h-2v-2h-2l-7-8-51-51v-2h-2v-2l-4-1-5-1v275l-1 94h-1l-1-332v-41l-4 1z"
                                    fill="#7FC79D"
                                />
                                <path
                                    transform="translate(1300,930)"
                                    d="m0 0 25 1 2 2-1 4h-3l-1 5h-2l-2 4h-2l-1 3-5 5h-2l-1 3-13 13h-2l-2 4-5 4-7 8-36 36h-2l-2 4-36 36h-2l-1 3-6 5-6 7-8 7-11 11-5 6-6 5-6 7-3 2h-5l2-2 2 1 1-5 4-1 1-3 4-1 2-5 5-6 6-5 2-4h2l1-3 3-1 6-8h3l1-4h3v-3l3-1 2-4h2l2-4 3-3 3-1 6-7 2-1 1-3 3-1 1-3 3-1 1-3 3-1 1-3 3-1 1-3 3-1 6-7 6-5 6-7h3l1-4 4-4h3l1-4 7-6v-2l3-1 2-3h2l1-4 4-1v-3l7-6 2-3h2l2-4h2l1-4h3l1-5 4-2v-2h3l1-3 2-2 2-1v-2h3l2-6-23-1z"
                                    fill="#E65025"
                                />
                                <path
                                    transform="translate(540,677)"
                                    d="m0 0h1l2 18 2 41v78l-1 36v8l-2 3-1 6-2-4v-182z"
                                    fill="#00A4F4"
                                />
                                <path
                                    transform="translate(566,222)"
                                    d="m0 0 2 1-4 4-2 5-2 4-6 4h-2l-2 4-12 12h-2l-2 4-8 8h-2l-2 4h-2l-2 4h-2v2h-2v2h-2v2h-2v2h-2v2h-2v2l-7 6-14 14-1 2h-2v2h-2v2l-7 6-48 48-5 6-8 7-6 7h-2v2l-5 5-3 1v2h-2v2h-2v2h-2l-2 4-6 7-6 5-1 3-4-2 6-8 171-171 4-5 8-7z"
                                    fill="#7BCDE3"
                                />
                                <path
                                    transform="translate(691,95)"
                                    d="m0 0 2 1-98 98-1 2h-2l-2 4-22 22-8 7-10 9-7 8-166 166-6 7-3-1 77-77 5-6 6-5 7-8 136-136h2l1-3 6-5 6-7 8-7z"
                                    fill="#FDFDFD"
                                />
                                <path
                                    transform="translate(1143,1120)"
                                    d="m0 0 3 2-7 7-8 7-39 39-7 8-149 149-6 7-8 7-4 5-2-1 9-10 26-26 2-5v-18h1l1 14 5-2 8-7 6-7 6-5 5-6 7-6 3-4h2l2-4h2l2-4 20-20h2l2-4 56-56 5-6 8-7 4-5 8-7 11-11 7-8 13-12z"
                                    fill="#7DC8AB"
                                />
                                <path
                                    transform="translate(177,609)"
                                    d="m0 0 2 3 1 3v45l1 268 3 1-3 3-9-7 2-1h4l-1-37v-57l1-211-1-3-5 1-4 5-4 4h-2l-2 4-5 4-2-1z"
                                    fill="#7BCEE4"
                                />
                                <path
                                    transform="translate(948,231)"
                                    d="m0 0h2l1 41 1 337-2-1-1-64-1-183z"
                                    fill="#6ECAC2"
                                />
                                <path
                                    transform="translate(1337,612)"
                                    d="m0 0 4 2 1 2-5-1 1 25v284l5-2 9-9 2 1-13 13-5 2-1-1v-307z"
                                    fill="#7BCEE4"
                                />
                                <path
                                    transform="translate(962,237)"
                                    d="m0 0 4 2 178 178-2 5-2-4-5-6-3-2v-2l-4-2v-2h-2l-4-4v-2h-2v-2h-2l-4-4v-2h-2v-2l-4-2-8-8v-2l-4-2-16-16v-2l-4-2v-2l-4-2v-2l-4-2v-2l-4-2v-2l-4-2v-2l-4-2v-2l-4-2-4-4v-2l-4-2v-2l-4-2v-2l-4-2v-2h-2v-2h-2v-2h-2v-2h-2v-2h-2v-2h-2v-2h-2l-7-8-51-51v-2h-2z"
                                    fill="#9DCA95"
                                />
                                <path
                                    transform="translate(751,681)"
                                    d="m0 0 92 2 14 2v1l-5 1h-123l-3-3 15-2z"
                                    fill="#00A3F3"
                                />
                                <path
                                    transform="translate(485,658)"
                                    d="m0 0h1l1 8v22l-103-1-2-1 1-1 54-2h31l6-3 5-7 2-10h2v-3h2z"
                                    fill="#00A3F2"
                                />
                                <path
                                    transform="translate(598,676)"
                                    d="m0 0h1v140h-1l-1-14-1-7-2-25v-35l2-33 1-4z"
                                    fill="#00A4F4"
                                />
                                <path
                                    transform="translate(755,34)"
                                    d="m0 0 3 1-4 4-5 3-6 7-5 5-3 1-2 4h-2l-2 4-15 15-5 3-7 8-107 107v-3l97-97 3-5 8-7 33-33 8-7 9-9z"
                                    fill="#89D3E6"
                                />
                                <path
                                    transform="translate(375,420)"
                                    d="m0 0 3 2v2h2l6 7 62 62 7 8 23 23v2h2v2h2v2h2v2h2v2h2v2h2l7 8 3 2v2h2l7 8 10 10-1 2-57-57-7-8-80-80z"
                                    fill="#84C79A"
                                />
                                <path
                                    transform="translate(722,789)"
                                    d="m0 0h1v53h-1v-5l-4 1-2-12 1-20 1-12 3-1z"
                                    fill="#00A2EF"
                                />
                                <path
                                    transform="translate(1225,703)"
                                    d="m0 0 3 3 5 11 5 14 4 19 1 8v26l-5 25-6 17-1-3 6-20 2-11v-38l-4-23-5-15-5-11z"
                                    fill="#7BCEE4"
                                />
                                <path
                                    transform="translate(722,689)"
                                    d="m0 0h1v52h-1l-1-7-5-2-1-15 2-16 3-4h2z"
                                    fill="#00A2F0"
                                />
                                <path
                                    transform="translate(465,758)"
                                    d="m0 0h1l1 32h-8l-8-2 1-3h2l1-4 3-6 2-9 3-6z"
                                    fill="#00A3F1"
                                />
                                <path
                                    transform="translate(369,746)"
                                    d="m0 0h98l1 4-1 37h-1l-1-29-3-1v-2h-2l-3-6v-2h-88z"
                                    fill="#46BCE6"
                                />
                                <path
                                    transform="translate(177,609)"
                                    d="m0 0 2 3 1 3v25l-1 20h-1v-41l-1-3-5 1-4 5-4 4h-2l-2 4-5 4-2-1z"
                                    fill="#7BCEE4"
                                />
                                <path
                                    transform="translate(874,769)"
                                    d="m0 0 5 1v15l-7 1v-2h-6l4-4z"
                                    fill="#00A3F2"
                                />
                                <path
                                    transform="translate(755,34)"
                                    d="m0 0 3 1-4 4-5 3-6 7-5 5-3 1-2 4-5 3-12 12-2-1 22-22 8-7 9-9z"
                                    fill="#ADE0ED"
                                />
                                <path
                                    transform="translate(957,927)"
                                    d="m0 0h82v1l-81 1 2 5-5-2-2-4z"
                                    fill="#EA7928"
                                />
                                <path
                                    transform="translate(952,1312)"
                                    d="m0 0 2 1-1 3-8 7-9 9-6 7-8 7-4 5-2-1 9-10 26-26z"
                                    fill="#75CCD3"
                                />
                                <path
                                    transform="translate(485,644)"
                                    d="m0 0 3 1v13l-1 8h-1l-1-10-3-5-2-1v-5z"
                                    fill="#14A9EB"
                                />
                                <path
                                    transform="translate(888,678)"
                                    d="m0 0 3 1 1 8-1 1h-10l1-4 3-5z"
                                    fill="#00A2EE"
                                />
                                <path
                                    transform="translate(539,644)"
                                    d="m0 0h3l1 3v12l-2 10h-1l-1-7z"
                                    fill="#00A4F4"
                                />
                                <path
                                    transform="translate(177,609)"
                                    d="m0 0 2 4-6 3-5 6-4 4h-2l-2 4-5 4-2-1z"
                                    fill="#CBEBF3"
                                />
                                <path
                                    transform="translate(542,890)"
                                    d="m0 0h2v2l19 1-2 2-23 1 1-5z"
                                    fill="#00A3F3"
                                />
                                <path
                                    transform="translate(779,1486)"
                                    d="m0 0v3l-10 10-2 3h-3l-1 3-2-1v-5l6-3 5-3z"
                                    fill="#3FBEE9"
                                />
                                <path
                                    transform="translate(1155,1104)"
                                    d="m0 0 4 1-7 6-4 4h-5l2-2 2 1 1-5 4-1 1-3z"
                                    fill="#E64D27"
                                />
                                <path
                                    transform="translate(876,743)"
                                    d="m0 0h4l-1 8-4 1-3-8z"
                                    fill="#00A3F1"
                                />
                                <path
                                    transform="translate(854,516)"
                                    d="m0 0 4 2 13 13-3 1-2-4-4-2v-2l-4-2v-2l-4-2z"
                                    fill="#D14D56"
                                />
                                <path
                                    transform="translate(594,890)"
                                    d="m0 0h5l1 1v5h-8v-3z"
                                    fill="#00A2EF"
                                />
                                <path
                                    transform="translate(1337,612)"
                                    d="m0 0 4 2 1 2-5-1v25h-1l-1-19z"
                                    fill="#8BD4E7"
                                />
                                <path
                                    transform="translate(930,1335)"
                                    d="m0 0h2l-2 4-8 7-4 5-2-1 9-10z"
                                    fill="#76CCD7"
                                />
                                <path
                                    transform="translate(1143,1121)"
                                    d="m0 0 3 1-7 7-4 2 2-4z"
                                    fill="#7DC9B4"
                                />
                            </svg>
                        </div>
                    </main>
                </div>
                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </>
    );
}
