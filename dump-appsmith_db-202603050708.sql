--
-- PostgreSQL database dump
--

\restrict gOtc9YKK5JJXOoJBd2Ds8THqvM3OdmXZvJZ0zCKjGxwyfHGFn0ROAzMJz61IscZ

-- Dumped from database version 15.17 (Debian 15.17-1.pgdg13+1)
-- Dumped by pg_dump version 17.8 (Debian 17.8-0+deb13u1)

-- Started on 2026-03-05 07:08:44 -04

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
-- TOC entry 9 (class 2615 OID 33901)
-- Name: topology; Type: SCHEMA; Schema: -; Owner: admin
--

CREATE SCHEMA topology;


ALTER SCHEMA topology OWNER TO admin;

--
-- TOC entry 5092 (class 0 OID 0)
-- Dependencies: 9
-- Name: SCHEMA topology; Type: COMMENT; Schema: -; Owner: admin
--

COMMENT ON SCHEMA topology IS 'PostGIS Topology schema';


--
-- TOC entry 2 (class 3079 OID 32820)
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- TOC entry 5093 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry and geography spatial types and functions';


--
-- TOC entry 4 (class 3079 OID 34077)
-- Name: postgis_raster; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis_raster WITH SCHEMA public;


--
-- TOC entry 5094 (class 0 OID 0)
-- Dependencies: 4
-- Name: EXTENSION postgis_raster; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis_raster IS 'PostGIS raster types and functions';


--
-- TOC entry 3 (class 3079 OID 33902)
-- Name: postgis_topology; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis_topology WITH SCHEMA topology;


--
-- TOC entry 5095 (class 0 OID 0)
-- Dependencies: 3
-- Name: EXTENSION postgis_topology; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis_topology IS 'PostGIS topology spatial types and functions';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 221 (class 1259 OID 32770)
-- Name: alcaldiainfraestructura; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.alcaldiainfraestructura (
    id integer NOT NULL,
    alcaldia character(14) NOT NULL,
    fechaingreso date DEFAULT CURRENT_DATE NOT NULL,
    areadato character(2) NOT NULL,
    gestionpor character(2) NOT NULL,
    clasedato character(2) NOT NULL,
    datoespecifico text NOT NULL,
    tipovalordato character(2) NOT NULL,
    valordato text NOT NULL,
    parroquia character(20)
);


ALTER TABLE public.alcaldiainfraestructura OWNER TO admin;

--
-- TOC entry 220 (class 1259 OID 32769)
-- Name: alcaldiainfraestructura_id_seq; Type: SEQUENCE; Schema: public; Owner: admin
--

ALTER TABLE public.alcaldiainfraestructura ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.alcaldiainfraestructura_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 219 (class 1259 OID 24585)
-- Name: users; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.users (
    id integer NOT NULL,
    first_name text NOT NULL,
    last_name text NOT NULL,
    email text NOT NULL,
    username text,
    password_hash text NOT NULL,
    last_login timestamp with time zone,
    role text DEFAULT 'user'::text NOT NULL,
    created timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.users OWNER TO admin;

--
-- TOC entry 218 (class 1259 OID 24584)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: admin
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO admin;

--
-- TOC entry 5096 (class 0 OID 0)
-- Dependencies: 218
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: admin
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 4905 (class 2604 OID 24588)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 5086 (class 0 OID 32770)
-- Dependencies: 221
-- Data for Name: alcaldiainfraestructura; Type: TABLE DATA; Schema: public; Owner: admin
--



--
-- TOC entry 4901 (class 0 OID 33139)
-- Dependencies: 223
-- Data for Name: spatial_ref_sys; Type: TABLE DATA; Schema: public; Owner: admin
--



--
-- TOC entry 5084 (class 0 OID 24585)
-- Dependencies: 219
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: admin
--

INSERT INTO public.users VALUES (1, 'RAFAEL', 'DIAZ', 'Rdiaz', 'Rdiaz', '123456', NULL, 'user', '2026-03-02 20:54:26.028142+00');
INSERT INTO public.users VALUES (3, 'Rafael', 'Diaz', 'admin', NULL, '$2a$10$gIKdsOgILLstfhCjUrJ5PuVEqlfG/uTd3SHWv28lSPCkPn3AVBjXe', '2026-03-02 21:03:33.599+00', 'Admin', '2026-03-02 21:03:18.635389+00');


--
-- TOC entry 4903 (class 0 OID 33904)
-- Dependencies: 228
-- Data for Name: topology; Type: TABLE DATA; Schema: topology; Owner: admin
--



--
-- TOC entry 4904 (class 0 OID 33917)
-- Dependencies: 229
-- Data for Name: layer; Type: TABLE DATA; Schema: topology; Owner: admin
--



--
-- TOC entry 5097 (class 0 OID 0)
-- Dependencies: 220
-- Name: alcaldiainfraestructura_id_seq; Type: SEQUENCE SET; Schema: public; Owner: admin
--

SELECT pg_catalog.setval('public.alcaldiainfraestructura_id_seq', 5, true);


--
-- TOC entry 5098 (class 0 OID 0)
-- Dependencies: 218
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: admin
--

SELECT pg_catalog.setval('public.users_id_seq', 3, true);


--
-- TOC entry 5099 (class 0 OID 0)
-- Dependencies: 227
-- Name: topology_id_seq; Type: SEQUENCE SET; Schema: topology; Owner: admin
--

SELECT pg_catalog.setval('topology.topology_id_seq', 1, false);


--
-- TOC entry 4921 (class 2606 OID 32777)
-- Name: alcaldiainfraestructura alcaldiainfraestructura_pkey; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.alcaldiainfraestructura
    ADD CONSTRAINT alcaldiainfraestructura_pkey PRIMARY KEY (id);


--
-- TOC entry 4915 (class 2606 OID 24596)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 4917 (class 2606 OID 24594)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 4919 (class 2606 OID 24598)
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- TOC entry 4922 (class 1259 OID 32780)
-- Name: idx_alcaldia_area; Type: INDEX; Schema: public; Owner: admin
--

CREATE INDEX idx_alcaldia_area ON public.alcaldiainfraestructura USING btree (alcaldia, areadato);


--
-- TOC entry 4923 (class 1259 OID 32779)
-- Name: idx_fecha_ingreso; Type: INDEX; Schema: public; Owner: admin
--

CREATE INDEX idx_fecha_ingreso ON public.alcaldiainfraestructura USING btree (fechaingreso);


-- Completed on 2026-03-05 07:08:44 -04

--
-- PostgreSQL database dump complete
--

\unrestrict gOtc9YKK5JJXOoJBd2Ds8THqvM3OdmXZvJZ0zCKjGxwyfHGFn0ROAzMJz61IscZ

