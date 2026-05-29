package sv.edu.udb.Servicios;


import io.jsonwebtoken.JwtParser;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.Claims;
import io.jsonwebtoken.security.Keys;
import jakarta.annotation.PostConstruct;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.security.core.userdetails.User;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.stereotype.Service;

import javax.crypto.SecretKey;
import java.util.Date;

/* Servicio que hace 3 cosas importantes:
    * Extraer claims (cuerpo del token)
    * Generar token
    * Validar token
 */

@Service
public class JwtService {

    @Value("${jwt.secret}")
    private String secret;
    @Value("${jwt.expiration}")
    private long expiration; //En milisegundos
    @Value("${jwt.refresh-expiration}")
    private long refreshExpiration; //En milisegundos

    private SecretKey secretKey;
    private JwtParser jwtParser;

    @PostConstruct
    public void init() {
        // Creo la llave en application.properties en bites para convertirla
        // en un secret de 256 bites con la cual firmar el token
        this.secretKey = Keys.hmacShaKeyFor(secret.getBytes());
        // Creo el parser para parsear (Convertir)el token
        this.jwtParser = Jwts.parser().setSigningKey(secretKey).build();
    }

    public String generarToken(String username) {
        return Jwts.builder()
                .subject(username)
                .issuedAt(new Date())
                .expiration(new Date(System.currentTimeMillis() + expiration))
                .signWith(secretKey)
                .compact();
    }

    public String generarRefreshToken(String username) {
        return Jwts.builder()
                .subject(username)
                .issuedAt(new Date())
                .expiration(new Date(System.currentTimeMillis() + refreshExpiration))
                .signWith(secretKey)
                .compact();
    }

    //Se parsea el token (convertir) y se extrae el sujeto (username)
    public Claims extraerClaims (String token){
        return jwtParser.parseClaimsJws(token).getBody();
    }

    public String extraerUsername(String token){
        return extraerClaims(token).getSubject();
    }

    public boolean validarFechaToken(String token) {
        return !extraerClaims(token).getExpiration().before(new Date()); //Si la expiracion no es menor a la fecha actual
    }

    //Validamos que el nombre de usuario sea el mismo que el del token y que la fecha de expiracion no haya pasado
    public boolean validarToken(String token, UserDetails usuario){
        final String username = extraerUsername(token);
        return (username.equalsIgnoreCase(usuario.getUsername()) && validarFechaToken(token));
    }

}
