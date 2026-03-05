--
-- PostgreSQL database dump
--

\restrict UuwhfXDtRj81emdIshdtBiy03MukFExoiRtXyqk2ExXcZwneM4mOxws5lq60VMC

-- Dumped from database version 15.17 (Debian 15.17-1.pgdg13+1)
-- Dumped by pg_dump version 17.8 (Debian 17.8-0+deb13u1)

-- Started on 2026-03-05 07:11:45 -04

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 10 (class 2615 OID 32805)
-- Name: tiger; Type: SCHEMA; Schema: -; Owner: admin
--

CREATE SCHEMA tiger;


ALTER SCHEMA tiger OWNER TO admin;

--
-- TOC entry 4536 (class 0 OID 35733)
-- Dependencies: 228
-- Data for Name: geocode_settings; Type: TABLE DATA; Schema: tiger; Owner: admin
--



--
-- TOC entry 4537 (class 0 OID 36038)
-- Dependencies: 273
-- Data for Name: pagc_gaz; Type: TABLE DATA; Schema: tiger; Owner: admin
--



--
-- TOC entry 4538 (class 0 OID 36048)
-- Dependencies: 275
-- Data for Name: pagc_lex; Type: TABLE DATA; Schema: tiger; Owner: admin
--



--
-- TOC entry 4539 (class 0 OID 36058)
-- Dependencies: 277
-- Data for Name: pagc_rules; Type: TABLE DATA; Schema: tiger; Owner: admin
--



-- Completed on 2026-03-05 07:11:45 -04

--
-- PostgreSQL database dump complete
--

\unrestrict UuwhfXDtRj81emdIshdtBiy03MukFExoiRtXyqk2ExXcZwneM4mOxws5lq60VMC

