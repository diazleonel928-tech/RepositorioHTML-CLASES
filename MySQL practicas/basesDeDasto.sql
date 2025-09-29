---------------------------------------------------
-- TABLA INVENTORY_LIST
---------------------------------------------------
CREATE TABLE inventory_list (
    id_invent_list VARCHAR2(11) NOT NULL,
    cost NUMBER(7,2) NOT NULL,
    stock NUMBER(4) NOT NULL,
    CONSTRAINT id_invent_list_pk PRIMARY KEY (id_invent_list)
);

---------------------------------------------------
-- TABLA ITEM
---------------------------------------------------
CREATE TABLE item (
    itm_number VARCHAR2(10) NOT NULL,
    name VARCHAR2(20) NOT NULL,
    description VARCHAR2(50) NOT NULL,
    category VARCHAR2(25),
    color VARCHAR2(15),
    size CHAR(1),
    ilt_id VARCHAR2(11) NOT NULL,
    CONSTRAINT item_pk PRIMARY KEY (itm_number),
    CONSTRAINT item_invent_list_fk FOREIGN KEY (ilt_id)
        REFERENCES inventory_list(id_invent_list)
);

---------------------------------------------------
-- TABLA PRICE_HISTORY
---------------------------------------------------
CREATE TABLE price_history (
    itm_number VARCHAR2(10) NOT NULL,
    start_date DATE NOT NULL,
    start_time DATE NOT NULL,
    price NUMBER(7,2) NOT NULL,
    end_date DATE,
    end_time DATE,
    CONSTRAINT price_history_pk PRIMARY KEY (itm_number, start_date, start_time),
    CONSTRAINT price_history_item_fk FOREIGN KEY (itm_number)
        REFERENCES item(itm_number)
);

---------------------------------------------------
-- TABLA CLIENTE
---------------------------------------------------
CREATE TABLE cliente (
    id_cliente VARCHAR2(11) NOT NULL,
    email VARCHAR2(50) NOT NULL,
    apellido VARCHAR2(30) NOT NULL,
    nombre VARCHAR2(30) NOT NULL,
    telefono VARCHAR2(15) NOT NULL,
    balance NUMBER(10,2),
    CONSTRAINT cliente_pk PRIMARY KEY (id_cliente)
);

---------------------------------------------------
-- TABLA INDIVIDUAL
---------------------------------------------------
CREATE TABLE individual (
    id_cliente VARCHAR2(11) NOT NULL,
    t_lealtad VARCHAR2(20),
    CONSTRAINT individual_pk PRIMARY KEY (id_cliente),
    CONSTRAINT individual_cliente_fk FOREIGN KEY (id_cliente)
        REFERENCES cliente(id_cliente)
);

---------------------------------------------------
-- TABLA TEAM_REP
---------------------------------------------------
CREATE TABLE team_rep (
    id_cliente VARCHAR2(11) NOT NULL,
    descuento NUMBER(5,2) NOT NULL,
    CONSTRAINT team_rep_pk PRIMARY KEY (id_cliente),
    CONSTRAINT team_rep_cliente_fk FOREIGN KEY (id_cliente)
        REFERENCES cliente(id_cliente)
);

---------------------------------------------------
-- TABLA EQUIPO
---------------------------------------------------
CREATE TABLE equipo (
    id_equipo VARCHAR2(11) NOT NULL,
    nombre VARCHAR2(30) NOT NULL,
    num_jugadores NUMBER(3) NOT NULL,
    descuento NUMBER(5,2),
    CONSTRAINT equipo_pk PRIMARY KEY (id_equipo)
);

---------------------------------------------------
-- TABLA SALES_REP
---------------------------------------------------
CREATE TABLE sales_rep (
    id_sales_rep VARCHAR2(11) NOT NULL,
    apellido VARCHAR2(30) NOT NULL,
    nombre VARCHAR2(30) NOT NULL,
    telefono VARCHAR2(15),
    comision NUMBER(5,2) NOT NULL,
    supervisor VARCHAR2(11),
    CONSTRAINT sales_rep_pk PRIMARY KEY (id_sales_rep),
    CONSTRAINT sales_rep_supervisor_fk FOREIGN KEY (supervisor)
        REFERENCES sales_rep(id_sales_rep)
);

---------------------------------------------------
-- TABLA ADDRESS_CLI
---------------------------------------------------
CREATE TABLE address_cli (
    id_add_cli    VARCHAR2(11) NOT NULL,
    domicilio_1   VARCHAR2(50) NOT NULL,
    domicilio_2   VARCHAR2(50),
    ciudad        VARCHAR2(30) NOT NULL,
    codigo_postal VARCHAR2(10) NOT NULL,
    id_cliente    VARCHAR2(11) NOT NULL,
    CONSTRAINT address_cli_pk PRIMARY KEY (id_add_cli),
    CONSTRAINT address_cli_cliente_fk FOREIGN KEY (id_cliente)
        REFERENCES cliente(id_cliente)
);

---------------------------------------------------
-- TABLA ORDEN
---------------------------------------------------
CREATE TABLE orden (
    id_orden      VARCHAR2(11) NOT NULL,
    fecha         DATE NOT NULL,
    hora          DATE NOT NULL,
    num_articulos NUMBER(4) NOT NULL,
    id_cliente    VARCHAR2(11) NOT NULL,
    id_sales_rep  VARCHAR2(11) NOT NULL,
    CONSTRAINT orden_pk PRIMARY KEY (id_orden),
    CONSTRAINT orden_cliente_fk FOREIGN KEY (id_cliente)
        REFERENCES cliente(id_cliente),
    CONSTRAINT orden_salesrep_fk FOREIGN KEY (id_sales_rep)
        REFERENCES sales_rep(id_sales_rep)
);

---------------------------------------------------
-- TABLA ARTICULO
---------------------------------------------------
CREATE TABLE articulo (
    id_articulo   VARCHAR2(11) NOT NULL,
    nombre        VARCHAR2(30) NOT NULL,
    descripcion   VARCHAR2(100) NOT NULL,
    categoria     VARCHAR2(30),
    color         VARCHAR2(20),
    size          CHAR(1),
    CONSTRAINT articulo_pk PRIMARY KEY (id_articulo)
);

---------------------------------------------------
-- TABLA CANT_ART (DETALLE ORDEN - ARTICULO)
---------------------------------------------------
CREATE TABLE cant_art (
    id_orden       VARCHAR2(11) NOT NULL,
    id_articulo    VARCHAR2(11) NOT NULL,
    cant_ordenada  NUMBER(4) NOT NULL,
    cant_enviada   NUMBER(4) NOT NULL,
    CONSTRAINT cant_art_pk PRIMARY KEY (id_orden, id_articulo),
    CONSTRAINT cant_art_orden_fk FOREIGN KEY (id_orden)
        REFERENCES orden(id_orden),
    CONSTRAINT cant_art_articulo_fk FOREIGN KEY (id_articulo)
        REFERENCES articulo(id_articulo)
);

---------------------------------------------------
-- TABLA INVENTARIO
---------------------------------------------------
CREATE TABLE inventario (
    id_inv       VARCHAR2(11) NOT NULL,
    costoXunidad NUMBER(7,2) NOT NULL,
    stock        NUMBER(4) NOT NULL,
    id_articulo  VARCHAR2(11) NOT NULL,
    CONSTRAINT inventario_pk PRIMARY KEY (id_inv),
    CONSTRAINT inventario_articulo_fk FOREIGN KEY (id_articulo)
        REFERENCES articulo(id_articulo)
);

---------------------------------------------------
-- TABLA HIST_PRECIO
---------------------------------------------------
CREATE TABLE hist_precio (
    id_hist_price VARCHAR2(11) NOT NULL,
    start_date    DATE NOT NULL,
    start_time    DATE NOT NULL,
    end_date      DATE,
    end_time      DATE,
    price         NUMBER(7,2) NOT NULL,
    id_articulo   VARCHAR2(11) NOT NULL,
    CONSTRAINT hist_precio_pk PRIMARY KEY (id_hist_price),
    CONSTRAINT hist_precio_articulo_fk FOREIGN KEY (id_articulo)
        REFERENCES articulo(id_articulo)
);
