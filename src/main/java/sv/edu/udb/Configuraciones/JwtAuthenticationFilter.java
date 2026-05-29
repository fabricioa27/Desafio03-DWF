package sv.edu.udb.Configuraciones;

/*
    * Esta clase sirve para crear un nuevo filtro en el filter chain (de Spring Security)
    * que se encarga de la autenticacion de los tokens JWT.
*/

import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import lombok.RequiredArgsConstructor;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.core.userdetails.UserDetailsService;
import org.springframework.security.web.authentication.WebAuthenticationDetailsSource;
import org.springframework.stereotype.Component;
import org.springframework.web.filter.OncePerRequestFilter;
import sv.edu.udb.Servicios.JwtService;

import java.io.IOException;

// Es oncePerRequestFilter para que el filtro se ejecute una vez por cada peticion
@Component
@RequiredArgsConstructor
public class JwtAuthenticationFilter extends OncePerRequestFilter {

    private final JwtService jwtService;
    private final UserDetailsService userDetailsService;

    // Ocupamos los servlets porque queremos acceder a las peticiones de los usuarios para validarlos
    // response servlet es para enviar la respuesta al cliente como querramos, y request es lo que nos manda el usuario
    // filterChain es un objeto que nos envia spring security para que podamos continuar con el filtro
    @Override
    protected void doFilterInternal(HttpServletRequest request, HttpServletResponse response, FilterChain filterChain) throws ServletException, IOException {
        final String authHeader = request.getHeader("Authorization"); //Leemos el header de autorizacion donde esta el JWT token
        final String jwt;
        final String username;

        // Si el token esta mal formado, no es un token, o no es un token valido, no lo procesamos
        // y dejamos que los controladores se encarguen de rechazar la peticion
        if (authHeader == null || !authHeader.startsWith("Bearer ")) {
            filterChain.doFilter(request, response);
            return;
        }

        jwt = authHeader.substring(7).trim();
        
        try {
            username = jwtService.extraerUsername(jwt);
        } catch (Exception e) {
            filterChain.doFilter(request, response); //Continuamos con el siguiente filtro
            return;
        }

        if (username != null && SecurityContextHolder.getContext().getAuthentication() == null) {
            UserDetails userDetails = userDetailsService.loadUserByUsername(username);
            
            if (jwtService.validarToken(jwt, userDetails)) {
                // Este objeto es el que guardaremos en el Context Holder
                // Agregamos detalles por si se llegaran a ocupar (ip, headers, etc)
                UsernamePasswordAuthenticationToken authToken = new UsernamePasswordAuthenticationToken(
                        userDetails,
                        null,
                        userDetails.getAuthorities()
                );
                authToken.setDetails(
                        new WebAuthenticationDetailsSource().buildDetails(request)
                );
                //Lo guardamos en un contenedor el cual permite que los controladores verifiquen si este usuario esta autenticado
                SecurityContextHolder.getContext().setAuthentication(authToken);
            }
        }
        
        filterChain.doFilter(request, response);
    }
}
